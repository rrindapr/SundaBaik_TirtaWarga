<?php

namespace App\Http\Controllers\bpbd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $mainStats = [
            ['label' => 'Total laporan',           'value' => '48',        'change' => '+20%', 'isGood' => true],
            ['label' => 'Warga terdampak',         'value' => '1.284 KK',  'change' => '+12%', 'isGood' => false],
            ['label' => 'Rata-rata waktu tanggap', 'value' => '18 j 40 m', 'change' => '-35%', 'isGood' => true],
        ];

        $statusStats = [
            ['label' => 'Menunggu',     'value' => '14', 'change' => '+8%',  'isGood' => false],
            ['label' => 'Diverifikasi', 'value' => '21', 'change' => '+15%', 'isGood' => true],
            ['label' => 'Selesai',      'value' => '13', 'change' => '+30%', 'isGood' => true],
        ];

        $weeklyChart = [
            ['day' => 'Sen, 26 Okt', 'received' => 3,  'completed' => 1],
            ['day' => 'Sel, 27 Okt', 'received' => 8,  'completed' => 2],
            ['day' => 'Rab, 28 Okt', 'received' => 10, 'completed' => 0],
            ['day' => 'Kam, 29 Okt', 'received' => 5,  'completed' => 3],
            ['day' => 'Jum, 30 Okt', 'received' => 8,  'completed' => 1],
            ['day' => 'Sab, 31 Okt', 'received' => 5,  'completed' => 3],
            ['day' => 'Min, 1 Nov',  'received' => 0,  'completed' => 0],
        ];

        $areas = [
            ['name' => 'Antapani Kidul', 'district' => 'Antapani',     'reports' => 12, 'affected' => 412, 'status' => 'Menunggu',     'color' => 'coral'],
            ['name' => 'Sukamiskin',     'district' => 'Arcamanik',    'reports' => 10, 'affected' => 356, 'status' => 'Diverifikasi', 'color' => 'blue'],
            ['name' => 'Babakan Sari',   'district' => 'Kiaracondong', 'reports' => 9,  'affected' => 268, 'status' => 'Diverifikasi', 'color' => 'green'],
            ['name' => 'Cicaheum',       'district' => 'Kiaracondong', 'reports' => 7,  'affected' => 248, 'status' => 'Selesai',      'color' => 'pink'],
        ];

        $totalAffected = array_sum(array_column($areas, 'affected'));

        foreach ($areas as &$area) {
            $area['percent'] = $totalAffected > 0 ? round($area['affected'] / $totalAffected * 100) : 0;
        }
        unset($area);

        $chartMax = 10;

        return view('bpbd.dashboard', compact(
            'mainStats', 'statusStats', 'weeklyChart', 'areas', 'totalAffected', 'chartMax'
        ));
    }
}
