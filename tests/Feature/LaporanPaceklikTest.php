<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LaporanPaceklikTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: buat user dengan role tertentu.
     */
    private function createUser(string $role = 'rt'): User
    {
        return User::create([
            'name'     => "Test User {$role}",
            'email'    => "{$role}_" . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role'     => $role,
        ]);
    }

    // ---------------------------------------------------------------
    // PBI 10 — Menyimpan data ke tabel laporan_paceklik
    // ---------------------------------------------------------------

    #[Test]
    public function laporan_berhasil_disimpan_ke_database(): void
    {
        $user = $this->createUser('rt');

        $response = $this->actingAs($user)->post(route('laporan-paceklik.store'), [
            'alamat'           => 'Jl. Antapani Lama No. 12',
            'jumlah_terdampak' => 42,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('laporan_paceklik', [
            'user_id'           => $user->id,
            'alamat'            => 'Jl. Antapani Lama No. 12',
            'jumlah_terdampak'  => 42,
            'status'            => 'menunggu',
        ]);
    }

    #[Test]
    public function user_bpbd_juga_bisa_membuat_laporan(): void
    {
        $user = $this->createUser('bpbd');

        $response = $this->actingAs($user)->post(route('laporan-paceklik.store'), [
            'alamat'           => 'Jl. Sukamiskin No. 5',
            'jumlah_terdampak' => 30,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('laporan_paceklik', [
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function validasi_alamat_wajib_diisi(): void
    {
        $user = $this->createUser('rt');

        $response = $this->actingAs($user)->post(route('laporan-paceklik.store'), [
            'jumlah_terdampak' => 10,
        ]);

        $response->assertSessionHasErrors('alamat');
    }

    #[Test]
    public function validasi_jumlah_terdampak_wajib_diisi(): void
    {
        $user = $this->createUser('rt');

        $response = $this->actingAs($user)->post(route('laporan-paceklik.store'), [
            'alamat' => 'Jl. Test',
        ]);

        $response->assertSessionHasErrors('jumlah_terdampak');
    }

    #[Test]
    public function validasi_jumlah_terdampak_minimal_1(): void
    {
        $user = $this->createUser('rt');

        $response = $this->actingAs($user)->post(route('laporan-paceklik.store'), [
            'alamat'           => 'Jl. Test',
            'jumlah_terdampak' => 0,
        ]);

        $response->assertSessionHasErrors('jumlah_terdampak');
    }

    #[Test]
    public function user_belum_login_tidak_bisa_membuat_laporan(): void
    {
        $response = $this->post(route('laporan-paceklik.store'), [
            'alamat'           => 'Jl. Test',
            'jumlah_terdampak' => 10,
        ]);

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // PBI 8 — ID laporan unik otomatis format LP-YYYY-NNNN
    // ---------------------------------------------------------------

    #[Test]
    public function id_laporan_otomatis_tergenerate_dengan_format_benar(): void
    {
        $user = $this->createUser('rt');

        $laporan = LaporanPaceklik::create([
            'user_id'           => $user->id,
            'alamat'            => 'Jl. Test',
            'jumlah_terdampak'  => 10,
        ]);

        $tahun = now()->year;
        $this->assertEquals("LP-{$tahun}-0001", $laporan->id_laporan);
    }

    #[Test]
    public function id_laporan_auto_increment_per_tahun(): void
    {
        $user = $this->createUser('rt');

        $laporan1 = LaporanPaceklik::create([
            'user_id'           => $user->id,
            'alamat'            => 'Jl. Test 1',
            'jumlah_terdampak'  => 10,
        ]);

        $laporan2 = LaporanPaceklik::create([
            'user_id'           => $user->id,
            'alamat'            => 'Jl. Test 2',
            'jumlah_terdampak'  => 20,
        ]);

        $laporan3 = LaporanPaceklik::create([
            'user_id'           => $user->id,
            'alamat'            => 'Jl. Test 3',
            'jumlah_terdampak'  => 30,
        ]);

        $tahun = now()->year;
        $this->assertEquals("LP-{$tahun}-0001", $laporan1->id_laporan);
        $this->assertEquals("LP-{$tahun}-0002", $laporan2->id_laporan);
        $this->assertEquals("LP-{$tahun}-0003", $laporan3->id_laporan);
    }

    #[Test]
    public function id_laporan_unik_tidak_duplikat(): void
    {
        $user = $this->createUser('rt');

        $ids = [];
        for ($i = 0; $i < 5; $i++) {
            $laporan = LaporanPaceklik::create([
                'user_id'           => $user->id,
                'alamat'            => "Jl. Test {$i}",
                'jumlah_terdampak'  => 10 + $i,
            ]);
            $ids[] = $laporan->id_laporan;
        }

        // Semua ID harus unik
        $this->assertCount(5, array_unique($ids));
    }

    // ---------------------------------------------------------------
    // PBI 11 — Tanggal laporan otomatis
    // ---------------------------------------------------------------

    #[Test]
    public function tanggal_laporan_terisi_otomatis_saat_create(): void
    {
        $user = $this->createUser('rt');

        $before = now();

        $laporan = LaporanPaceklik::create([
            'user_id'           => $user->id,
            'alamat'            => 'Jl. Test',
            'jumlah_terdampak'  => 10,
        ]);

        $after = now();

        $this->assertNotNull($laporan->tanggal_laporan);
        $this->assertTrue($laporan->tanggal_laporan->greaterThanOrEqualTo($before->subSecond()));
        $this->assertTrue($laporan->tanggal_laporan->lessThanOrEqualTo($after->addSecond()));
    }

    #[Test]
    public function tanggal_laporan_tidak_bisa_dimanipulasi_dari_request(): void
    {
        $user = $this->createUser('rt');

        $fakeDate = '2020-01-01 00:00:00';

        $this->actingAs($user)->post(route('laporan-paceklik.store'), [
            'alamat'            => 'Jl. Test',
            'jumlah_terdampak'  => 10,
            'tanggal_laporan'   => $fakeDate, // coba manipulasi
        ]);

        $laporan = LaporanPaceklik::first();

        // tanggal_laporan harus diisi server, bukan dari input user
        $this->assertNotEquals($fakeDate, $laporan->tanggal_laporan->format('Y-m-d H:i:s'));
        $this->assertTrue($laporan->tanggal_laporan->isToday());
    }

    #[Test]
    public function relasi_user_berfungsi(): void
    {
        $user = $this->createUser('rt');

        $laporan = LaporanPaceklik::create([
            'user_id'           => $user->id,
            'alamat'            => 'Jl. Test',
            'jumlah_terdampak'  => 10,
        ]);

        $this->assertEquals($user->id, $laporan->user->id);
        $this->assertTrue($user->laporanPacekliks->contains($laporan));
    }
}
