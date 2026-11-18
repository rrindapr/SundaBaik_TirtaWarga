<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\PenetapanBantuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// PBI-23: detail wilayah terpilih penerima bantuan
class DetailPenetapanTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(User $rt, string $kode): LaporanPaceklik
    {
        return LaporanPaceklik::create([
            'kode'                => $kode,
            'user_id'             => $rt->id,
            'alamat'              => 'Jl. Uji ' . $kode,
            'jumlah_kk_terdampak' => 42,
            'keterangan'          => 'Sumur warga kering sejak dua minggu.',
            'status'              => 1,
            'tingkat_keparahan'   => 'Kritis',
        ]);
    }

    private function tetapkan(LaporanPaceklik $laporan, string $nomor, string $status = 'Aktif'): PenetapanBantuan
    {
        return PenetapanBantuan::create([
            'laporan_id'           => $laporan->id,
            'nomor_surat_tugas'    => $nomor,
            'petugas_bpbd'         => 'Petugas Uji',
            'alokasi_tangki_liter' => 10000,
            'jadwal_distribusi'    => now()->addDays(3)->toDateString(),
            'catatan_verifikasi'   => 'Titik kumpul di balai RT.',
            'status_bantuan'       => $status,
        ]);
    }

    public function test_bpbd_melihat_detail_wilayah_terpilih(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt', 'name' => 'Ketua RT Antapani']);
        $laporan = $this->makeReport($rt, 'LP-PBI23-0001');
        $penetapan = $this->tetapkan($laporan, 'ST/BPBD/TES/10001');

        $this->actingAs($bpbd)
            ->get(route('bpbd.penetapan.show', $penetapan))
            ->assertOk()
            ->assertSee('ST/BPBD/TES/10001')
            ->assertSee('LP-PBI23-0001')
            ->assertSee('Jl. Uji LP-PBI23-0001')
            ->assertSee('Ketua RT Antapani')
            ->assertSee('Sumur warga kering sejak dua minggu.')
            ->assertSee('Titik kumpul di balai RT.')
            ->assertSee('10.000 liter')
            ->assertSee('Kritis')
            ->assertSee('3 hari lagi');
    }

    public function test_daftar_penetapan_menautkan_ke_detail(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);
        $penetapan = $this->tetapkan($this->makeReport($rt, 'LP-PBI23-0002'), 'ST/BPBD/TES/10002');

        $this->actingAs($bpbd)
            ->get(route('bpbd.penetapan.index'))
            ->assertOk()
            ->assertSee(route('bpbd.penetapan.show', $penetapan), false);
    }

    public function test_peta_tampil_jika_laporan_punya_koordinat(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);
        $laporan = $this->makeReport($rt, 'LP-PBI23-0003');
        $laporan->forceFill(['latitude' => -6.9147, 'longitude' => 107.6098])->save();
        $penetapan = $this->tetapkan($laporan, 'ST/BPBD/TES/10003');

        $this->actingAs($bpbd)
            ->get(route('bpbd.penetapan.show', $penetapan))
            ->assertOk()
            ->assertSee('id="detailPeta"', false)
            ->assertDontSee('belum memiliki titik koordinat');
    }

    public function test_pesan_tanpa_koordinat_jika_laporan_belum_punya_titik(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);
        $penetapan = $this->tetapkan($this->makeReport($rt, 'LP-PBI23-0004'), 'ST/BPBD/TES/10004');

        $this->actingAs($bpbd)
            ->get(route('bpbd.penetapan.show', $penetapan))
            ->assertOk()
            ->assertSee('belum memiliki titik koordinat')
            ->assertDontSee('id="detailPeta"', false);
    }

    public function test_riwayat_penetapan_lain_untuk_wilayah_yang_sama_ditampilkan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);
        $laporan = $this->makeReport($rt, 'LP-PBI23-0005');
        $this->tetapkan($laporan, 'ST/BPBD/TES/10005', 'Dibatalkan');
        $aktif = $this->tetapkan($laporan, 'ST/BPBD/TES/10006', 'Aktif');

        $this->actingAs($bpbd)
            ->get(route('bpbd.penetapan.show', $aktif))
            ->assertOk()
            ->assertSee('ST/BPBD/TES/10006')
            ->assertSee('ST/BPBD/TES/10005')
            ->assertSee('Dibatalkan');
    }

    public function test_penetapan_yang_tidak_ada_menghasilkan_404(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->get('/bpbd/penetapan/99999')
            ->assertNotFound();
    }

    public function test_ketua_rt_tidak_bisa_membuka_detail_penetapan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);
        $penetapan = $this->tetapkan($this->makeReport($rt, 'LP-PBI23-0007'), 'ST/BPBD/TES/10007');

        $this->actingAs($rt)
            ->get(route('bpbd.penetapan.show', $penetapan))
            ->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);
        $penetapan = $this->tetapkan($this->makeReport($rt, 'LP-PBI23-0008'), 'ST/BPBD/TES/10008');

        $this->get(route('bpbd.penetapan.show', $penetapan))
            ->assertRedirect(route('login'));
    }
}