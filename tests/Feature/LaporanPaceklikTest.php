<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanPaceklikTest extends TestCase
{
    use RefreshDatabase;

    public function test_rt_bisa_menyimpan_laporan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), [
                'alamat' => 'Jl. Antapani Lama No. 12',
                'jumlah_kk_terdampak' => 42,
            ])
            ->assertRedirect(route('rt.dashboard'));

        $this->assertDatabaseHas('laporan_paceklik', [
            'user_id' => $rt->id,
            'kode' => 'LP-' . now()->year . '-0001',
            'jumlah_kk_terdampak' => 42,
            'status' => 1,
        ]);
    }

    public function test_bpbd_tidak_boleh_mengirim_laporan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->post(route('rt.laporan.store'), [
                'alamat' => 'x',
                'jumlah_kk_terdampak' => 1,
            ])
            ->assertForbidden();
    }
}