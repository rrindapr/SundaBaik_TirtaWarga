<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI-17: Menampilkan titik lokasi laporan di peta.
 */
class PetaTitikLokasiTest extends TestCase
{
    use RefreshDatabase;

    private function buatLaporan(User $rt, string $kode, ?float $lat, ?float $lng): LaporanPaceklik
    {
        $laporan = LaporanPaceklik::create([
            'kode'                => $kode,
            'user_id'             => $rt->id,
            'alamat'              => 'Jl. Antapani Lama No. 12',
            'jumlah_kk_terdampak' => 42,
            'status'              => 1,
            'tingkat_keparahan'   => 'Kritis',
        ]);

        $laporan->forceFill(['latitude' => $lat, 'longitude' => $lng])->save();

        return $laporan;
    }

    public function test_laporan_dengan_koordinat_menjadi_titik_di_peta(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt   = User::factory()->create(['role' => 'rt']);
        $this->buatLaporan($rt, 'LP-TES-1001', -6.9150, 107.6600);

        $this->actingAs($bpbd)
            ->get(route('bpbd.peta.index'))
            ->assertOk()
            ->assertViewHas('titikLaporan', fn ($titik) => count($titik) === 1
                && $titik[0]['kode'] === 'LP-TES-1001'
                && $titik[0]['tingkat'] === 'Kritis')
            ->assertSee('LP-TES-1001');
    }

    public function test_laporan_tanpa_koordinat_tidak_digambar_tetapi_dihitung(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $rt   = User::factory()->create(['role' => 'rt']);
        $this->buatLaporan($rt, 'LP-TES-1002', -6.9150, 107.6600);
        $this->buatLaporan($rt, 'LP-TES-1003', null, null);

        $this->actingAs($bpbd)
            ->get(route('bpbd.peta.index'))
            ->assertViewHas('titikLaporan', fn ($titik) => count($titik) === 1)
            ->assertViewHas('tanpaKoordinat', 1);
    }

    public function test_peta_kosong_tetap_bisa_dibuka(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->get(route('bpbd.peta.index'))
            ->assertOk()
            ->assertViewHas('titikLaporan', fn ($titik) => count($titik) === 0);
    }
}