<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\PenetapanBantuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// PBI-22: daftar wilayah yang sudah ditetapkan sebagai penerima bantuan
class DaftarPenetapanTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(User $rt, string $kode, int $kk = 20): LaporanPaceklik
    {
        return LaporanPaceklik::create([
            'kode'                => $kode,
            'user_id'             => $rt->id,
            'alamat'              => 'Alamat ' . $kode,
            'jumlah_kk_terdampak' => $kk,
            'status'              => 1,
        ]);
    }

    private function tetapkan(LaporanPaceklik $laporan, string $nomor, string $status = 'Aktif', int $liter = 5000): PenetapanBantuan
    {
        return PenetapanBantuan::create([
            'laporan_id'           => $laporan->id,
            'nomor_surat_tugas'    => $nomor,
            'petugas_bpbd'         => 'Petugas Uji',
            'alokasi_tangki_liter' => $liter,
            'jadwal_distribusi'    => now()->addDay()->toDateString(),
            'status_bantuan'       => $status,
        ]);
    }

    public function test_bpbd_melihat_daftar_wilayah_yang_sudah_ditetapkan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt', 'name' => 'Ketua RT Cicaheum']);
        $laporan = $this->makeReport($rt, 'LP-PBI22-0001');
        $this->tetapkan($laporan, 'ST/BPBD/TES/00001');

        $this->actingAs($bpbd)
            ->get(route('bpbd.penetapan.index'))
            ->assertOk()
            ->assertSee('ST/BPBD/TES/00001')
            ->assertSee('LP-PBI22-0001')
            ->assertSee('Alamat LP-PBI22-0001')
            ->assertSee('Ketua RT Cicaheum')
            ->assertSee('Petugas Uji');
    }

    public function test_laporan_yang_belum_ditetapkan_tidak_muncul(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);
        $this->tetapkan($this->makeReport($rt, 'LP-PBI22-0002'), 'ST/BPBD/TES/00002');
        $this->makeReport($rt, 'LP-PBI22-0003');

        $this->actingAs($bpbd)
            ->get(route('bpbd.penetapan.index'))
            ->assertOk()
            ->assertSee('LP-PBI22-0002')
            ->assertDontSee('LP-PBI22-0003');
    }

    public function test_filter_berdasarkan_status_bantuan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);
        $this->tetapkan($this->makeReport($rt, 'LP-PBI22-0004'), 'ST/BPBD/TES/00004', 'Aktif');
        $this->tetapkan($this->makeReport($rt, 'LP-PBI22-0005'), 'ST/BPBD/TES/00005', 'Dibatalkan');

        $this->actingAs($bpbd)
            ->get(route('bpbd.penetapan.index', ['status' => 'Dibatalkan']))
            ->assertOk()
            ->assertSee('ST/BPBD/TES/00005')
            ->assertDontSee('ST/BPBD/TES/00004');
    }

    public function test_status_tidak_dikenal_dianggap_semua(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);
        $this->tetapkan($this->makeReport($rt, 'LP-PBI22-0006'), 'ST/BPBD/TES/00006');

        $this->actingAs($bpbd)
            ->get(route('bpbd.penetapan.index', ['status' => 'Ngawur']))
            ->assertOk()
            ->assertSee('ST/BPBD/TES/00006');
    }

    public function test_pencarian_berdasarkan_id_laporan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);
        $this->tetapkan($this->makeReport($rt, 'LP-PBI22-0007'), 'ST/BPBD/TES/00007');
        $this->tetapkan($this->makeReport($rt, 'LP-PBI22-0008'), 'ST/BPBD/TES/00008');

        $this->actingAs($bpbd)
            ->get(route('bpbd.penetapan.index', ['cari' => '0008']))
            ->assertOk()
            ->assertSee('ST/BPBD/TES/00008')
            ->assertDontSee('ST/BPBD/TES/00007');
    }

    public function test_ringkasan_tidak_menghitung_penetapan_dibatalkan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt = User::factory()->create(['role' => 'rt']);
        $this->tetapkan($this->makeReport($rt, 'LP-PBI22-0009', 30), 'ST/BPBD/TES/00009', 'Aktif', 10000);
        $this->tetapkan($this->makeReport($rt, 'LP-PBI22-0010', 25), 'ST/BPBD/TES/00010', 'Selesai', 5000);
        $this->tetapkan($this->makeReport($rt, 'LP-PBI22-0011', 99), 'ST/BPBD/TES/00011', 'Dibatalkan', 15000);

        $response = $this->actingAs($bpbd)->get(route('bpbd.penetapan.index'));

        $response->assertOk()
            ->assertViewHas('ringkasan', [
                'wilayah' => 2,
                'kk'      => 55,
                'liter'   => 15000,
            ]);
    }

    public function test_halaman_kosong_saat_belum_ada_penetapan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->get(route('bpbd.penetapan.index'))
            ->assertOk()
            ->assertSee('Belum ada wilayah yang ditetapkan');
    }

    public function test_menu_sidebar_penetapan_bantuan_mengarah_ke_daftar(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->get(route('bpbd.dashboard'))
            ->assertOk()
            ->assertSee(route('bpbd.penetapan.index'), false);
    }

    public function test_ketua_rt_tidak_bisa_membuka_daftar_penetapan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->get(route('bpbd.penetapan.index'))
            ->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('bpbd.penetapan.index'))
            ->assertRedirect(route('login'));
    }
}