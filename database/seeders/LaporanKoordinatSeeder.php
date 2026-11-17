<?php

namespace Database\Seeders;

use App\Models\LaporanPaceklik;
use Illuminate\Database\Seeder;

// PBI-17: koordinat dummy (perkiraan) untuk 5 laporan dari LaporanPaceklikSeeder.
// Dipisah dari seeder lama supaya tidak bentrok saat merge.
// Jalankan: php artisan db:seed --class=LaporanKoordinatSeeder
class LaporanKoordinatSeeder extends Seeder
{
    public function run(): void
    {
        $koordinat = [
            'LP-2026-0001' => [-6.9112, 107.6580],
            'LP-2026-0002' => [-6.9230, 107.6460],
            'LP-2026-0003' => [-6.9300, 107.6050],
            'LP-2026-0004' => [-6.9050, 107.5900],
            'LP-2026-0005' => [-6.9150, 107.6600],
        ];

        foreach ($koordinat as $kode => [$lat, $lng]) {
            LaporanPaceklik::where('kode', $kode)->update([
                'latitude'  => $lat,
                'longitude' => $lng,
            ]);
        }
    }
}