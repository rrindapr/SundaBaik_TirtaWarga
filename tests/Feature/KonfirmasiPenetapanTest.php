<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// PBI-21: konfirmasi penetapan bantuan oleh BPBD
class KonfirmasiPenetapanTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(string $kode = 'LP-PBI21-0001'): LaporanPaceklik
    {
        $rt = User::factory()->create(['role' => 'rt']);

        return LaporanPaceklik::create([
            'kode'                => $kode,
            'user_id'             => $rt->id,
            'alamat'              => 'Jl. Konfirmasi ' . $kode,
            'jumlah_kk_terdampak' => 35,
            'status'              => 1,
        ]);
    }

    private function payload(LaporanPaceklik $laporan, array $tambahan = []): array
    {
        return array_merge([
            'laporan_id'           => $laporan->id,
            'alokasi_tangki_liter' => 10000,
            'jadwal_distribusi'    => now()->addDays(2)->toDateString(),
            'catatan_verifikasi'   => 'Akses jalan muat truk tangki.',
        ], $tambahan);
    }

    public function test_halaman_laporan_masuk_memuat_dialog_konfirmasi(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $this->makeReport();

        $this->actingAs($bpbd)
            ->get(route('bpbd.laporan.index'))
            ->assertOk()
            ->assertSee('id="konfirmasiDialog"', false)
            ->assertSee('id="buktiDialog"', false)
            ->assertSee('onsubmit="bukaKonfirmasiPenetapan(event)"', false)
            ->assertSee('Ya, tetapkan');
    }

    public function test_penetapan_yang_dikonfirmasi_tersimpan_dan_mengembalikan_bukti(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd', 'name' => 'Petugas Konfirmasi']);
        $laporan = $this->makeReport();

        $response = $this->actingAs($bpbd)
            ->postJson(route('bpbd.penetapan.store'), $this->payload($laporan, ['konfirmasi' => true]))
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'data'    => [
                    'kode_laporan'         => 'LP-PBI21-0001',
                    'alamat'               => 'Jl. Konfirmasi LP-PBI21-0001',
                    'jumlah_kk_terdampak'  => 35,
                    'alokasi_tangki_liter' => 10000,
                    'jadwal_distribusi'    => now()->addDays(2)->format('d/m/Y'),
                    'petugas_bpbd'         => 'Petugas Konfirmasi',
                    'status_baru'          => 'Ditetapkan',
                ],
            ])
            ->assertJsonStructure(['data' => ['nomor_surat_tugas', 'penetapan_id', 'tanggal_penetapan', 'detail_url']]);

        $this->assertDatabaseHas('penetapan_bantuan', [
            'id'             => $response->json('data.penetapan_id'),
            'laporan_id'     => $laporan->id,
            'status_bantuan' => 'Aktif',
        ]);
    }

    public function test_penetapan_ditolak_jika_konfirmasi_tidak_dicentang(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $laporan = $this->makeReport();

        $this->actingAs($bpbd)
            ->postJson(route('bpbd.penetapan.store'), $this->payload($laporan, ['konfirmasi' => false]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['konfirmasi']);

        $this->assertDatabaseCount('penetapan_bantuan', 0);
    }

    public function test_pesan_error_konfirmasi_berbahasa_indonesia(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $laporan = $this->makeReport();

        $this->actingAs($bpbd)
            ->postJson(route('bpbd.penetapan.store'), $this->payload($laporan, ['konfirmasi' => 'tidak']))
            ->assertStatus(422)
            ->assertJsonPath('errors.konfirmasi.0', 'Penetapan belum dikonfirmasi. Centang pernyataan konfirmasi terlebih dahulu.');
    }

    public function test_konfirmasi_kedua_untuk_wilayah_yang_sama_tetap_ditolak(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);
        $laporan = $this->makeReport();

        $this->actingAs($bpbd)
            ->postJson(route('bpbd.penetapan.store'), $this->payload($laporan, ['konfirmasi' => true]))
            ->assertCreated();

        $this->actingAs($bpbd)
            ->postJson(route('bpbd.penetapan.store'), $this->payload($laporan, ['konfirmasi' => true]))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('penetapan_bantuan', 1);
    }
}