<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(User $rt, string $kode, int $status = 1): LaporanPaceklik
    {
        return LaporanPaceklik::create([
            'kode'                => $kode,
            'user_id'             => $rt->id,
            'alamat'              => 'Alamat ' . $kode,
            'jumlah_kk_terdampak' => 10,
            'status'              => $status,
        ]);
    }

    public function test_ketua_rt_hanya_melihat_laporan_miliknya(): void
    {
        $rtA = User::factory()->create(['role' => 'rt']);
        $rtB = User::factory()->create(['role' => 'rt']);
        $this->makeReport($rtA, 'LP-TES-0001');
        $this->makeReport($rtB, 'LP-TES-0002');

        $this->actingAs($rtA)
            ->get(route('rt.laporan.index'))
            ->assertOk()
            ->assertSee('LP-TES-0001')
            ->assertDontSee('LP-TES-0002');
    }

    public function test_status_angka_ditampilkan_sebagai_teks(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);
        $this->makeReport($rt, 'LP-TES-0003', 2);

        $this->actingAs($rt)
            ->get(route('rt.laporan.index'))
            ->assertOk()
            ->assertSee('Diverifikasi');
    }

    public function test_tampil_pesan_kosong_jika_belum_ada_laporan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->get(route('rt.laporan.index'))
            ->assertOk()
            ->assertSee('Belum ada laporan');
    }

    public function test_bpbd_tidak_bisa_membuka_riwayat_laporan_rt(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->get(route('rt.laporan.index'))
            ->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('rt.laporan.index'))
            ->assertRedirect(route('login'));
    }
}