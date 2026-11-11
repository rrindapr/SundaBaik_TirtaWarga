<?php

namespace Database\Seeders;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Database\Seeder;

class LaporanPaceklikSeeder extends Seeder
{
    /**
     * Seed data dummy laporan paceklik.
     */
    public function run(): void
    {
        // Ambil user RT yang sudah di-seed oleh UserSeeder
        $rtUsers = User::where('role', 'rt')->get();

        if ($rtUsers->isEmpty()) {
            $this->command->warn('Tidak ada user RT. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $dummyReports = [
            [
                'alamat'            => 'Jl. Antapani Lama No. 12, RT 05/RW 09',
                'jumlah_terdampak'  => 42,
            ],
            [
                'alamat'            => 'Gg. Sukaasih II, RT 03/RW 07',
                'jumlah_terdampak'  => 18,
            ],
            [
                'alamat'            => 'Jl. Purwakarta Blok C, RT 02/RW 04',
                'jumlah_terdampak'  => 25,
            ],
            [
                'alamat'            => 'Jl. Babakan Sari III No. 8, RT 01/RW 06',
                'jumlah_terdampak'  => 37,
            ],
            [
                'alamat'            => 'Jl. Sukamaju No. 15, RT 04/RW 02',
                'jumlah_terdampak'  => 15,
            ],
        ];

        foreach ($dummyReports as $i => $data) {
            // Distribusikan laporan ke user RT secara bergantian
            $user = $rtUsers[$i % $rtUsers->count()];

            LaporanPaceklik::create([
                'user_id'           => $user->id,
                'alamat'            => $data['alamat'],
                'jumlah_terdampak'  => $data['jumlah_terdampak'],
            ]);
        }
    }
}
