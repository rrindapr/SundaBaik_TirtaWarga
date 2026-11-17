<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

// PBI-08: pemberian ID laporan unik
class KodeLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function buatLaporan(?string $kode = null): LaporanPaceklik
    {
        $rt = User::where('role', 'rt')->first() ?? User::factory()->create(['role' => 'rt']);

        return LaporanPaceklik::create(array_filter([
            'kode'                => $kode,
            'user_id'             => $rt->id,
            'alamat'              => 'Jl. Contoh No. 1',
            'jumlah_kk_terdampak' => 10,
        ]));
    }

    public function test_kode_dibuat_otomatis_dengan_format_benar(): void
    {
        $laporan = $this->buatLaporan();

        $this->assertSame('LP-' . now()->year . '-0001', $laporan->kode);
    }

    public function test_kode_berurutan_dan_tidak_ada_yang_kembar(): void
    {
        $kode = collect(range(1, 5))->map(fn () => $this->buatLaporan()->kode);

        $this->assertCount(5, $kode->unique());
        $this->assertSame('LP-' . now()->year . '-0005', $kode->last());
    }

    public function test_kode_tidak_bentrok_setelah_laporan_di_tengah_dihapus(): void
    {
        $this->buatLaporan();
        $tengah = $this->buatLaporan();
        $this->buatLaporan();

        $tengah->delete();

        $this->assertSame('LP-' . now()->year . '-0004', $this->buatLaporan()->kode);
    }

    public function test_kode_yang_diisi_manual_tidak_ditimpa(): void
    {
        $this->assertSame('LP-TES-0099', $this->buatLaporan('LP-TES-0099')->kode);
    }

    public function test_nomor_dimulai_lagi_dari_0001_di_tahun_baru(): void
    {
        $this->buatLaporan();

        $this->travelTo(Carbon::create(now()->year + 1, 1, 5));

        $this->assertSame('LP-' . now()->year . '-0001', $this->buatLaporan()->kode);
    }

    public function test_kode_tidak_bisa_diubah_lewat_form_ketua_rt(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)->post(route('rt.laporan.store'), [
            'alamat'              => 'Jl. Antapani Lama No. 12',
            'jumlah_kk_terdampak' => 5,
            'kode'                => 'LP-HACK-9999',
        ])->assertRedirect(route('rt.dashboard'));

        $this->assertDatabaseMissing('laporan_paceklik', ['kode' => 'LP-HACK-9999']);
        $this->assertDatabaseHas('laporan_paceklik', ['kode' => 'LP-' . now()->year . '-0001']);
    }
}
