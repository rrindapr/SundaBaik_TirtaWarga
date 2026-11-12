<?php

namespace Database\Seeders;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Database\Seeder;

class LaporanPaceklikSeeder extends Seeder
{
    /**
     * Seed data dummy laporan paceklik.
     * Status: 1 = Menunggu, 2 = Diverifikasi, 3 = Selesai.
     */
    public function run(): void
    {
        // Ambil user RT yang sudah di-seed oleh UserSeeder
        $rtUsers = User::where('role', 'rt')->orderBy('id')->get();

        if ($rtUsers->isEmpty()) {
            $this->command->warn('Tidak ada user RT. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $dummyReports = [
            ['kode' => 'LP-2026-0001', 'alamat' => 'Jl. Purwakarta Blok C, RT 05/RW 09',      'jumlah_kk_terdampak' => 25, 'status' => 3, 'tanggal' => '2026-10-12 16:30'],
            ['kode' => 'LP-2026-0002', 'alamat' => 'Jl. Babakan Sari III No. 8, RT 03/RW 04', 'jumlah_kk_terdampak' => 37, 'status' => 3, 'tanggal' => '2026-10-20 07:05'],
            ['kode' => 'LP-2026-0003', 'alamat' => 'Gg. Sukaasih II, RT 05/RW 09',            'jumlah_kk_terdampak' => 18, 'status' => 1, 'tanggal' => '2026-10-27 09:40'],
            ['kode' => 'LP-2026-0004', 'alamat' => 'Jl. Sukamaju No. 15, RT 03/RW 04',        'jumlah_kk_terdampak' => 15, 'status' => 1, 'tanggal' => '2026-10-29 10:20'],
            ['kode' => 'LP-2026-0005', 'alamat' => 'Jl. Antapani Lama No. 12, RT 05/RW 09',   'jumlah_kk_terdampak' => 42, 'status' => 2, 'tanggal' => '2026-10-30 08:15'],
        ];

        foreach ($dummyReports as $i => $data) {
            // Distribusikan laporan ke user RT secara bergantian
            $user = $rtUsers[$i % $rtUsers->count()];

            $laporan = LaporanPaceklik::updateOrCreate(
                ['kode' => $data['kode']],
                [
                    'user_id'             => $user->id,
                    'alamat'              => $data['alamat'],
                    'jumlah_kk_terdampak' => $data['jumlah_kk_terdampak'],
                    'status'              => $data['status'],
                ]
            );

            // created_at dipakai sebagai tanggal lapor
            $laporan->forceFill([
                'created_at' => $data['tanggal'],
                'updated_at' => $data['tanggal'],
            ])->save();
        }
    }
}