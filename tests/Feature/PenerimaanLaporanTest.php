<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// PBI-07: endpoint penerima data laporan
class PenerimaanLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return array_merge([
            'alamat' => 'Jl. Antapani Lama No. 12',
            'jumlah_kk_terdampak' => 42,
            'keterangan' => 'Sumur warga mengering sejak dua minggu.',
        ], $extra);
    }

    public function test_endpoint_menerima_dan_menyimpan_laporan_ketua_rt(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->postJson(route('rt.laporan.terima'), $this->payload())
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'kode' => 'LP-' . now()->year . '-0001',
                    'alamat' => 'Jl. Antapani Lama No. 12',
                    'jumlah_kk_terdampak' => 42,
                    'keterangan' => 'Sumur warga mengering sejak dua minggu.',
                ],
            ]);

        $this->assertDatabaseHas('laporan_paceklik', [
            'user_id' => $rt->id,
            'alamat' => 'Jl. Antapani Lama No. 12',
            'jumlah_kk_terdampak' => 42,
        ]);
    }

    public function test_respons_memuat_status_dan_label_serta_tanggal_lapor(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->postJson(route('rt.laporan.terima'), $this->payload())
            ->assertJsonPath('data.status', 1)
            ->assertJsonPath('data.status_label', 'Menunggu')
            ->assertJsonStructure([
                'data' => ['tanggal_lapor'],
            ]);
    }

    public function test_data_tidak_valid_ditolak_dengan_422(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->postJson(route('rt.laporan.terima'), [
                'alamat' => '',
                'jumlah_kk_terdampak' => 0,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'alamat',
                'jumlah_kk_terdampak',
            ]);

        $this->assertDatabaseCount('laporan_paceklik', 0);
    }

    public function test_kode_berurutan_untuk_kiriman_berturut_turut(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->postJson(route('rt.laporan.terima'), $this->payload())
            ->assertCreated();

        $this->actingAs($rt)
            ->postJson(
                route('rt.laporan.terima'),
                $this->payload(['alamat' => 'Gg. Sukaasih II'])
            )
            ->assertCreated()
            ->assertJsonPath(
                'data.kode',
                'LP-' . now()->year . '-0002'
            );

        $this->assertSame(2, LaporanPaceklik::count());
    }

    public function test_bpbd_tidak_boleh_mengirim_lewat_endpoint(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->postJson(route('rt.laporan.terima'), $this->payload())
            ->assertForbidden();

        $this->assertDatabaseCount('laporan_paceklik', 0);
    }

    public function test_tamu_tidak_bisa_mengirim_lewat_endpoint(): void
    {
        $this->postJson(route('rt.laporan.terima'), $this->payload())
            ->assertUnauthorized();

        $this->assertDatabaseCount('laporan_paceklik', 0);
    }
}
