<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

//penerusan laporan Ketua RT ke daftar laporan masuk BPBD
class PenerusanLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return array_merge([
            'alamat'              => 'Jl. Antapani Lama No. 12, RT 05/RW 09',
            'jumlah_kk_terdampak' => 42,
            'keterangan'          => 'Sumur warga kering sejak dua minggu.',
        ], $extra);
    }

    public function test_laporan_dari_form_rt_langsung_masuk_daftar_bpbd(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt', 'name' => 'Ketua RT Antapani Kidul']);

        $this->actingAs($rt)->post(route('rt.laporan.store'), $this->payload());

        $laporan = LaporanPaceklik::firstOrFail();

        $this->actingAs($bpbd)
            ->get(route('bpbd.laporan.index'))
            ->assertOk()
            ->assertSee($laporan->kode)
            ->assertSee('Ketua RT Antapani Kidul')
            ->assertSee('Jl. Antapani Lama No. 12, RT 05/RW 09')
            ->assertSee('Menunggu');
    }

    public function test_laporan_dari_endpoint_penerimaan_juga_masuk_daftar_bpbd(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt', 'name' => 'Ketua RT Babakan Sari']);

        $kode = $this->actingAs($rt)
            ->postJson(route('rt.laporan.terima'), $this->payload(['alamat' => 'Jl. Sukamaju No. 15']))
            ->assertCreated()
            ->json('data.kode');

        $this->actingAs($bpbd)
            ->get(route('bpbd.laporan.index'))
            ->assertOk()
            ->assertSee($kode)
            ->assertSee('Ketua RT Babakan Sari')
            ->assertSee('Jl. Sukamaju No. 15');
    }

    public function test_laporan_dari_beberapa_rt_semuanya_diteruskan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rtA = User::factory()->create(['role' => 'rt']);
        $rtB = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rtA)->post(route('rt.laporan.store'), $this->payload(['alamat' => 'Alamat RT A']));
        $this->actingAs($rtB)->post(route('rt.laporan.store'), $this->payload(['alamat' => 'Alamat RT B']));

        $this->assertDatabaseCount('laporan_paceklik', 2);

        $this->actingAs($bpbd)
            ->get(route('bpbd.laporan.index'))
            ->assertSee('Alamat RT A')
            ->assertSee('Alamat RT B')
            ->assertSee('2 laporan');
    }

    public function test_badge_bpbd_menghitung_laporan_baru_yang_belum_ditindaklanjuti(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);

        // Dua laporan baru (Menunggu) dan satu laporan yang sudah selesai.
        $this->actingAs($rt)->post(route('rt.laporan.store'), $this->payload());
        $this->actingAs($rt)->post(route('rt.laporan.store'), $this->payload());
        LaporanPaceklik::create([
            'user_id'             => $rt->id,
            'alamat'              => 'Laporan lama',
            'jumlah_kk_terdampak' => 10,
            'status'              => 3,
        ]);

        $this->actingAs($bpbd)
            ->get(route('bpbd.dashboard'))
            ->assertOk()
            ->assertSee('<span class="count">2</span>', false);
    }

    public function test_badge_berkurang_setelah_laporan_ditetapkan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)->post(route('rt.laporan.store'), $this->payload());
        $laporan = LaporanPaceklik::firstOrFail();

        $this->actingAs($bpbd)
            ->get(route('bpbd.dashboard'))
            ->assertSee('<span class="count">1</span>', false);

        $this->actingAs($bpbd)->postJson(route('bpbd.penetapan.store'), [
            'laporan_id'           => $laporan->id,
            'alokasi_tangki_liter' => 5000,
            'jadwal_distribusi'    => now()->addDay()->toDateString(),
        ])->assertCreated();

        $this->actingAs($bpbd)
            ->get(route('bpbd.dashboard'))
            ->assertDontSee('<span class="count">1</span>', false);
    }

    public function test_detail_rt_menampilkan_laporan_sudah_diteruskan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)->post(route('rt.laporan.store'), $this->payload());
        $laporan = LaporanPaceklik::firstOrFail();

        $this->actingAs($rt)
            ->get(route('rt.laporan.show', $laporan->kode))
            ->assertOk()
            ->assertSee('Laporan telah diteruskan ke BPBD')
            ->assertSee($laporan->logStatus->first()->created_at->format('d/m/Y H.i'));
    }

    public function test_ketua_rt_tidak_bisa_membuka_daftar_bpbd(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)->post(route('rt.laporan.store'), $this->payload());

        $this->actingAs($rt)
            ->get(route('bpbd.laporan.index'))
            ->assertForbidden();
    }
}