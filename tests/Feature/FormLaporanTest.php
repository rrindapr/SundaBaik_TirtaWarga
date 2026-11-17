<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// PBI-01: form isian alamat dan jumlah terdampak
class FormLaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_rt_bisa_membuka_form_laporan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->get(route('rt.laporan.create'))
            ->assertOk()
            ->assertSee('name="alamat"', false)
            ->assertSee('name="jumlah_kk_terdampak"', false)
            ->assertSee('name="keterangan"', false)
            ->assertSee(route('rt.laporan.store'), false);
    }

    public function test_bpbd_tidak_boleh_membuka_form_laporan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->get(route('rt.laporan.create'))
            ->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('rt.laporan.create'))
            ->assertRedirect(route('login'));
    }

    public function test_url_buat_tidak_dianggap_kode_laporan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        // Pastikan /rt/laporan/buat tidak jatuh ke route show (/laporan/{kode}).
        $this->actingAs($rt)
            ->get('/rt/laporan/buat')
            ->assertOk();
    }

    public function test_isian_dipertahankan_saat_validasi_gagal(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->from(route('rt.laporan.create'))
            ->post(route('rt.laporan.store'), [
                'alamat' => 'Jl. Antapani Lama No. 12',
                'jumlah_kk_terdampak' => '',
            ])
            ->assertRedirect(route('rt.laporan.create'))
            ->assertSessionHasErrors('jumlah_kk_terdampak');

        $this->actingAs($rt)
            ->withSession(['_old_input' => ['alamat' => 'Jl. Antapani Lama No. 12']])
            ->get(route('rt.laporan.create'))
            ->assertSee('Jl. Antapani Lama No. 12');
    }
}