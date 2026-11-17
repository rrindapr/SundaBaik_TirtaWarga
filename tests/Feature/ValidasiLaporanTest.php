<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// PBI-02: validasi isian wajib pada form kirim laporan
class ValidasiLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function rt(): User
    {
        return User::factory()->create(['role' => 'rt']);
    }

    private function kirim(array $data)
    {
        return $this->actingAs($this->rt())
            ->from(route('rt.laporan.create'))
            ->post(route('rt.laporan.store'), $data);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'alamat'              => 'Jl. Antapani Lama No. 12',
            'jumlah_kk_terdampak' => 42,
        ], $override);
    }

    public function test_alamat_wajib_diisi(): void
    {
        $this->kirim($this->payload(['alamat' => '']))
            ->assertRedirect(route('rt.laporan.create'))
            ->assertSessionHasErrors(['alamat' => 'Alamat lengkap wajib diisi.']);

        $this->assertDatabaseCount('laporan_paceklik', 0);
    }

    public function test_alamat_hanya_spasi_ditolak(): void
    {
        $this->kirim($this->payload(['alamat' => '     ']))
            ->assertSessionHasErrors(['alamat' => 'Alamat lengkap wajib diisi.']);

        $this->assertDatabaseCount('laporan_paceklik', 0);
    }

    public function test_alamat_terlalu_pendek_ditolak(): void
    {
        $this->kirim($this->payload(['alamat' => 'abcd']))
            ->assertSessionHasErrors('alamat');
    }

    public function test_alamat_terlalu_panjang_ditolak(): void
    {
        $this->kirim($this->payload(['alamat' => str_repeat('a', 256)]))
            ->assertSessionHasErrors('alamat');
    }

    public function test_jumlah_kk_wajib_diisi(): void
    {
        $this->kirim($this->payload(['jumlah_kk_terdampak' => '']))
            ->assertSessionHasErrors(['jumlah_kk_terdampak' => 'Jumlah KK terdampak wajib diisi.']);

        $this->assertDatabaseCount('laporan_paceklik', 0);
    }

    public function test_jumlah_kk_harus_angka_bulat_positif(): void
    {
        foreach (['abc', '4.5', '0', '-3', '10001'] as $nilai) {
            $this->kirim($this->payload(['jumlah_kk_terdampak' => $nilai]))
                ->assertSessionHasErrors('jumlah_kk_terdampak');
        }

        $this->assertDatabaseCount('laporan_paceklik', 0);
    }

    public function test_keterangan_terlalu_panjang_ditolak(): void
    {
        $this->kirim($this->payload(['keterangan' => str_repeat('a', 1001)]))
            ->assertSessionHasErrors('keterangan');
    }

    public function test_keterangan_boleh_kosong(): void
    {
        $this->kirim($this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('rt.dashboard'));

        $this->assertDatabaseCount('laporan_paceklik', 1);
    }

    public function test_isian_valid_menyimpan_laporan(): void
    {
        $this->kirim($this->payload(['keterangan' => 'Sumur warga kering.']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('laporan_paceklik', [
            'alamat'              => 'Jl. Antapani Lama No. 12',
            'jumlah_kk_terdampak' => 42,
            'keterangan'          => 'Sumur warga kering.',
        ]);
    }

    public function test_form_menampilkan_pesan_error_dan_mempertahankan_isian(): void
    {
        $rt = $this->rt();

        $this->actingAs($rt)
            ->from(route('rt.laporan.create'))
            ->post(route('rt.laporan.store'), $this->payload(['jumlah_kk_terdampak' => '']));

        $this->actingAs($rt)
            ->get(route('rt.laporan.create'))
            ->assertOk()
            ->assertSee('Jumlah KK terdampak wajib diisi.')
            ->assertSee('Jl. Antapani Lama No. 12');
    }

    public function test_form_memiliki_atribut_required_di_browser(): void
    {
        $this->actingAs($this->rt())
            ->get(route('rt.laporan.create'))
            ->assertOk()
            ->assertSee('id="alamat" name="alamat"', false)
            ->assertSee('required', false);
    }

    public function test_endpoint_json_memakai_pesan_yang_sama(): void
    {
        $this->actingAs($this->rt())
            ->postJson(route('rt.laporan.terima'), ['alamat' => '', 'jumlah_kk_terdampak' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['alamat', 'jumlah_kk_terdampak']);
    }
}