<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI-18: Layer heatmap berdasarkan jumlah terdampak.
 */
class PetaHeatmapTest extends TestCase
{
    use RefreshDatabase;

    private function buatLaporan(User $rt, string $kode, int $kk, ?float $lat, ?float $lng): LaporanPaceklik
    {
        $laporan = LaporanPaceklik::create([
            'kode'                => $kode,
            'user_id'             => $rt->id,
            'alamat'              => 'Jl. Antapani Lama No. 12',
            'jumlah_kk_terdampak' => $kk,
            'status'              => 1,
            'tingkat_keparahan'   => 'Kritis',
        ]);

        $laporan->forceFill(['latitude' => $lat, 'longitude' => $lng])->save();

        return $laporan;
    }

    public function test_halaman_peta_memuat_legenda_dan_script_heatmap(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->get(route('bpbd.peta.index'))
            ->assertOk()
            ->assertSee('id="petaHeatLegend"', false)
            ->assertSee('js/peta-heatmap.js', false)
            ->assertSee('css/peta-heatmap.css', false);
    }

    public function test_data_titik_membawa_jumlah_kk_sebagai_bobot_heatmap(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt   = User::factory()->create(['role' => 'rt']);
        $this->buatLaporan($rt, 'LP-TES-1801', 68, -6.9150, 107.6600);
        $this->buatLaporan($rt, 'LP-TES-1802', 12, -6.9300, 107.6050);

        $this->actingAs($bpbd)
            ->get(route('bpbd.peta.index'))
            ->assertViewHas('titikLaporan', fn ($titik) => count($titik) === 2
                && collect($titik)->pluck('kk')->sort()->values()->all() === [12, 68]);
    }

    public function test_file_javascript_heatmap_tersedia(): void
    {
        $this->assertFileExists(public_path('js/peta-heatmap.js'));
        $this->assertFileExists(public_path('css/peta-heatmap.css'));
    }

    public function test_ketua_rt_tidak_bisa_membuka_peta_heatmap(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->get(route('bpbd.peta.index'))
            ->assertForbidden();
    }
}