<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// PBI-09: penetapan status awal "Menunggu"
class StatusAwalLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return array_merge([
            'alamat'              => 'Jl. Antapani Lama No. 12',
            'jumlah_kk_terdampak' => 42,
        ], $extra);
    }

    public function test_laporan_tanpa_status_otomatis_menunggu(): void
    {
        $user = User::factory()->create(['role' => 'rt']);

        $laporan = LaporanPaceklik::create([
            'user_id'             => $user->id,
            'alamat'              => 'Jl. Contoh No. 1',
            'jumlah_kk_terdampak' => 10,
        ]);

        $this->assertSame(1, $laporan->status);
        $this->assertSame(LaporanPaceklik::STATUS_MENUNGGU, $laporan->status);
        $this->assertSame('Menunggu', LaporanPaceklik::STATUS_LABELS[$laporan->status]);
        $this->assertDatabaseHas('laporan_paceklik', ['id' => $laporan->id, 'status' => 1]);
    }

    public function test_status_dari_form_ketua_rt_diabaikan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), $this->payload(['status' => 3]))
            ->assertRedirect(route('rt.dashboard'));

        $this->assertSame(1, LaporanPaceklik::firstOrFail()->status);
    }

    public function test_status_dari_endpoint_penerima_diabaikan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->postJson(route('rt.laporan.terima'), $this->payload(['status' => 3]))
            ->assertCreated()
            ->assertJsonPath('data.status', 1)
            ->assertJsonPath('data.status_label', 'Menunggu');

        $this->assertSame(1, LaporanPaceklik::firstOrFail()->status);
    }

    public function test_status_eksplisit_tetap_dihormati(): void
    {
        // Seeder dan riwayat laporan memang membutuhkan status selain Menunggu.
        $user = User::factory()->create(['role' => 'rt']);

        $laporan = LaporanPaceklik::create([
            'user_id'             => $user->id,
            'alamat'              => 'Jl. Contoh No. 2',
            'jumlah_kk_terdampak' => 10,
            'status'              => 2,
        ]);

        $this->assertSame(2, $laporan->fresh()->status);
    }
}