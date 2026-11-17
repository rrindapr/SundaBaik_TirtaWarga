<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

// PBI-11: pencatatan tanggal lapor otomatis
class TanggalLaporOtomatisTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return array_merge([
            'alamat'              => 'Jl. Antapani Lama No. 12',
            'jumlah_kk_terdampak' => 42,
        ], $extra);
    }

    public function test_tanggal_lapor_terisi_otomatis_saat_laporan_dibuat(): void
    {
        $this->travelTo(Carbon::parse('2026-11-17 10:30:00'));
        $user = User::factory()->create(['role' => 'rt']);

        $laporan = LaporanPaceklik::create([
            'user_id'             => $user->id,
            'alamat'              => 'Jl. Contoh No. 1',
            'jumlah_kk_terdampak' => 10,
        ]);

        $this->assertTrue($laporan->tanggal_lapor->equalTo(now()));
        $this->assertDatabaseHas('laporan_paceklik', [
            'id'            => $laporan->id,
            'tanggal_lapor' => '2026-11-17 10:30:00',
        ]);
    }

    public function test_tanggal_lapor_dari_form_ketua_rt_diabaikan(): void
    {
        $this->travelTo(Carbon::parse('2026-11-17 10:30:00'));
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), $this->payload([
                'tanggal_lapor' => '2020-01-01 00:00:00',
            ]));

        $this->assertDatabaseHas('laporan_paceklik', [
            'user_id'       => $rt->id,
            'tanggal_lapor' => '2026-11-17 10:30:00',
        ]);
    }

    public function test_respons_endpoint_memuat_tanggal_lapor_yang_tersimpan(): void
    {
        $this->travelTo(Carbon::parse('2026-11-17 10:30:00'));
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->postJson(route('rt.laporan.terima'), $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.tanggal_lapor', now()->toIso8601String());
    }

    public function test_tanggal_lapor_tidak_berubah_saat_laporan_diperbarui(): void
    {
        $this->travelTo(Carbon::parse('2026-11-17 10:30:00'));
        $user = User::factory()->create(['role' => 'rt']);

        $laporan = LaporanPaceklik::create([
            'user_id'             => $user->id,
            'alamat'              => 'Jl. Contoh No. 1',
            'jumlah_kk_terdampak' => 10,
        ]);

        $this->travelTo(Carbon::parse('2026-11-18 08:00:00'));
        $laporan->update(['alamat' => 'Jl. Contoh No. 2']);

        $this->assertDatabaseHas('laporan_paceklik', [
            'id'            => $laporan->id,
            'tanggal_lapor' => '2026-11-17 10:30:00',
        ]);
    }

    public function test_tanggal_lapor_yang_diisi_eksplisit_tidak_ditimpa(): void
    {
        $user = User::factory()->create(['role' => 'rt']);

        $laporan = new LaporanPaceklik([
            'user_id'             => $user->id,
            'alamat'              => 'Jl. Contoh No. 1',
            'jumlah_kk_terdampak' => 10,
        ]);
        $laporan->tanggal_lapor = Carbon::parse('2026-10-01 08:00:00');
        $laporan->save();

        $this->assertDatabaseHas('laporan_paceklik', [
            'id'            => $laporan->id,
            'tanggal_lapor' => '2026-10-01 08:00:00',
        ]);
    }
}