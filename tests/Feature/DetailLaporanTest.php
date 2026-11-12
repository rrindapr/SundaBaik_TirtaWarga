<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI-05: Detail laporan Ketua RT.
 */
class DetailLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(User $rt, string $kode, array $extra = []): LaporanPaceklik
    {
        return LaporanPaceklik::create(array_merge([
            'kode'                => $kode,
            'user_id'             => $rt->id,
            'alamat'              => 'Jl. Antapani Lama No. 12',
            'jumlah_kk_terdampak' => 42,
            'keterangan'          => 'Air mati sejak 3 hari lalu',
            'status'              => 1,
        ], $extra));
    }

    public function test_ketua_rt_bisa_melihat_detail_laporan_miliknya(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);
        $this->makeReport($rt, 'LP-TES-0001');

        $this->actingAs($rt)
            ->get(route('rt.laporan.show', 'LP-TES-0001'))
            ->assertOk()
            ->assertSee('LP-TES-0001')
            ->assertSee('Jl. Antapani Lama No. 12')
            ->assertSee('42 KK')
            ->assertSee('Air mati sejak 3 hari lalu')
            ->assertSee('Menunggu');
    }

    public function test_ketua_rt_tidak_bisa_membuka_laporan_milik_rt_lain(): void
    {
        $rtA = User::factory()->create(['role' => 'rt']);
        $rtB = User::factory()->create(['role' => 'rt']);
        $this->makeReport($rtB, 'LP-TES-0002');

        $this->actingAs($rtA)
            ->get(route('rt.laporan.show', 'LP-TES-0002'))
            ->assertNotFound();
    }

    public function test_kode_laporan_yang_tidak_ada_menghasilkan_404(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->get(route('rt.laporan.show', 'LP-TIDAK-ADA'))
            ->assertNotFound();
    }

    public function test_bpbd_tidak_bisa_membuka_detail_laporan_rt(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $this->makeReport($rt, 'LP-TES-0003');

        $this->actingAs($bpbd)
            ->get(route('rt.laporan.show', 'LP-TES-0003'))
            ->assertForbidden();
    }

    public function test_daftar_riwayat_memiliki_link_ke_detail(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);
        $this->makeReport($rt, 'LP-TES-0004');

        $this->actingAs($rt)
            ->get(route('rt.laporan.index'))
            ->assertOk()
            ->assertSee(route('rt.laporan.show', 'LP-TES-0004'), false);
    }
}