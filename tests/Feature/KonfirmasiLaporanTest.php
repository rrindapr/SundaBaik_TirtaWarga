<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// PBI-03: mengirim laporan dan menampilkan konfirmasi berhasil
class KonfirmasiLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return array_merge([
            'alamat'              => 'Jl. Antapani Lama No. 12',
            'jumlah_kk_terdampak' => 42,
        ], $extra);
    }

    public function test_setelah_kirim_diarahkan_ke_dashboard_dengan_data_konfirmasi(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);
        $kode = 'LP-' . now()->year . '-0001';

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), $this->payload())
            ->assertRedirect(route('rt.dashboard'))
            ->assertSessionHas('laporan_terkirim.kode', $kode)
            ->assertSessionHas('laporan_terkirim.status', 1);
    }

    public function test_konfirmasi_berhasil_menampilkan_id_laporan_dan_link_detail(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);
        $kode = 'LP-' . now()->year . '-0001';

        $this->actingAs($rt)
            ->followingRedirects()
            ->post(route('rt.laporan.store'), $this->payload())
            ->assertOk()
            ->assertSee('Laporan berhasil dikirim')
            ->assertSee($kode)
            ->assertSee(route('rt.laporan.show', $kode), false);
    }

    public function test_konfirmasi_hanya_tampil_sekali(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)->post(route('rt.laporan.store'), $this->payload());

        $this->get(route('rt.dashboard'))->assertSee('Laporan berhasil dikirim');
        $this->get(route('rt.dashboard'))->assertDontSee('Laporan berhasil dikirim');
    }

    public function test_dashboard_tanpa_pengiriman_tidak_menampilkan_konfirmasi(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->get(route('rt.dashboard'))
            ->assertOk()
            ->assertDontSee('Laporan berhasil dikirim');
    }

    public function test_validasi_gagal_tidak_menampilkan_konfirmasi(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->from(route('rt.laporan.create'))
            ->post(route('rt.laporan.store'), $this->payload(['jumlah_kk_terdampak' => '']))
            ->assertSessionHasErrors('jumlah_kk_terdampak')
            ->assertSessionMissing('laporan_terkirim');

        $this->assertDatabaseCount('laporan_paceklik', 0);
    }

    public function test_gagal_simpan_kembali_ke_form_dengan_isian_dipertahankan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        // Simulasi database gagal menyimpan.
        LaporanPaceklik::creating(function () {
            throw new \RuntimeException('simulasi gagal simpan');
        });

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), $this->payload())
            ->assertRedirect(route('rt.laporan.create'))
            ->assertSessionHas('laporan_gagal')
            ->assertSessionMissing('laporan_terkirim')
            ->assertSessionHasInput('alamat', 'Jl. Antapani Lama No. 12');

        $this->assertDatabaseCount('laporan_paceklik', 0);
    }

    public function test_form_menampilkan_pesan_gagal_kirim(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->withSession(['laporan_gagal' => 'Laporan gagal dikirim, coba lagi.'])
            ->get(route('rt.laporan.create'))
            ->assertOk()
            ->assertSee('Laporan gagal dikirim, coba lagi.');
    }
}