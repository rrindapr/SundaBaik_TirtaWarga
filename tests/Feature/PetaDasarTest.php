<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI-16: Menampilkan peta dasar (BPBD).
 */
class PetaDasarTest extends TestCase
{
    use RefreshDatabase;

    public function test_bpbd_bisa_membuka_halaman_peta_krisis(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->get(route('bpbd.peta.index'))
            ->assertOk()
            ->assertSee('id="petaKrisis"', false)
            ->assertSee('Peta Krisis');
    }

    public function test_menu_peta_krisis_ada_di_sidebar_bpbd(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->get(route('bpbd.peta.index'))
            ->assertSee(route('bpbd.peta.index'), false);
    }

    public function test_ketua_rt_tidak_bisa_membuka_peta_krisis(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->get(route('bpbd.peta.index'))
            ->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('bpbd.peta.index'))
            ->assertRedirect(route('login'));
    }
}