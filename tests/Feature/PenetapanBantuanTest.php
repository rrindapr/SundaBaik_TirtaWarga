<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// laporan masuk dan penetapan bantuan oleh BPBD
class PenetapanBantuanTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(User $rt, string $kode): LaporanPaceklik
    {
        return LaporanPaceklik::create([
            'kode'                => $kode,
            'user_id'             => $rt->id,
            'alamat'              => 'Alamat ' . $kode,
            'jumlah_kk_terdampak' => 20,
            'status'              => 1,
        ]);
    }

    private function penetapanData(LaporanPaceklik $laporan): array
    {
        return [
            'laporan_id'           => $laporan->id,
            'alokasi_tangki_liter' => 5000,
            'jadwal_distribusi'    => now()->addDay()->toDateString(),
            'catatan_verifikasi'   => 'Titik kumpul di balai RT.',
        ];
    }

    public function test_bpbd_melihat_semua_laporan_dari_semua_rt(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rtA = User::factory()->create(['role' => 'rt', 'name' => 'Ketua RT A']);
        $rtB = User::factory()->create(['role' => 'rt', 'name' => 'Ketua RT B']);
        $this->makeReport($rtA, 'LP-TES-0001');
        $this->makeReport($rtB, 'LP-TES-0002');

        $this->actingAs($bpbd)
            ->get(route('bpbd.laporan.index'))
            ->assertOk()
            ->assertSee('LP-TES-0001')
            ->assertSee('LP-TES-0002')
            ->assertSee('Ketua RT A')
            ->assertSee('Ketua RT B');
    }

    public function test_tamu_tidak_bisa_membuka_laporan_masuk_bpbd(): void
    {
        $this->get(route('bpbd.laporan.index'))
            ->assertRedirect(route('login'));
    }

    public function test_ketua_rt_tidak_bisa_membuka_laporan_masuk_bpbd(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->get(route('bpbd.laporan.index'))
            ->assertForbidden();
    }

    public function test_bpbd_bisa_menetapkan_bantuan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd', 'name' => 'Admin BPBD']);
        $rt = User::factory()->create(['role' => 'rt']);
        $laporan = $this->makeReport($rt, 'LP-TES-0003');

        $this->actingAs($bpbd)
            ->postJson(route('bpbd.penetapan.store'), $this->penetapanData($laporan))
            ->assertCreated()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('penetapan_bantuan', [
            'laporan_id'     => $laporan->id,
            'petugas_bpbd'   => 'Admin BPBD',
            'status_bantuan' => 'Aktif',
        ]);

        // Status laporan tidak berubah oleh penetapan (tetap 1 = Menunggu).
        $this->assertSame(1, $laporan->fresh()->status);
    }

    public function test_laporan_yang_sudah_ditetapkan_tidak_bisa_ditetapkan_lagi(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);
        $laporan = $this->makeReport($rt, 'LP-TES-0004');

        $this->actingAs($bpbd)->postJson(route('bpbd.penetapan.store'), $this->penetapanData($laporan))->assertCreated();

        $this->actingAs($bpbd)
            ->postJson(route('bpbd.penetapan.store'), $this->penetapanData($laporan))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('penetapan_bantuan', 1);
    }

    public function test_ketua_rt_tidak_bisa_menetapkan_bantuan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);
        $laporan = $this->makeReport($rt, 'LP-TES-0005');

        $this->actingAs($rt)
            ->postJson(route('bpbd.penetapan.store'), $this->penetapanData($laporan))
            ->assertForbidden();

        $this->assertDatabaseCount('penetapan_bantuan', 0);
    }

    public function test_tamu_tidak_bisa_menetapkan_bantuan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);
        $laporan = $this->makeReport($rt, 'LP-TES-0006');

        $this->postJson(route('bpbd.penetapan.store'), $this->penetapanData($laporan))
            ->assertUnauthorized();

        $this->assertDatabaseCount('penetapan_bantuan', 0);
    }
}