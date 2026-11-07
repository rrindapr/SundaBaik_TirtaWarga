<?php

namespace App\Http\Controllers\rt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            ['label' => 'Laporan dikirim',   'value' => '6', 'note' => '+2 laporan bulan ini'],
            ['label' => 'Sedang diproses',   'value' => '2', 'note' => 'Menunggu tindak lanjut BPBD'],
            ['label' => 'Selesai ditangani', 'value' => '4', 'note' => 'Bantuan sudah diterima'],
        ];

        //(step) 1 = Menunggu, 2 = Diverifikasi, 3 = Selesai
        $latestReport = [
            'code'     => 'LP-2026-0048',
            'sentAt'   => '30 Okt 2026, 08.15',
            'address'  => 'Jl. Antapani Lama No. 12, RT 05/RW 09',
            'affected' => 42,
            'step'     => 2, 
            'timeline' => [
                ['title' => 'Laporan dikirim',   'time' => '30 Okt, 08.15'],
                ['title' => 'Diverifikasi BPBD', 'time' => '30 Okt, 19.40'],
                ['title' => 'Bantuan selesai',   'time' => '—'],
            ],
        ];

        $reports = [
            ['code' => 'LP-2026-0048', 'date' => '30 Okt 2026', 'address' => 'Jl. Antapani Lama No. 12', 'affected' => 42, 'status' => 'Diverifikasi'],
            ['code' => 'LP-2026-0041', 'date' => '27 Okt 2026', 'address' => 'Gg. Sukaasih II',           'affected' => 18, 'status' => 'Menunggu'],
            ['code' => 'LP-2026-0029', 'date' => '20 Okt 2026', 'address' => 'Jl. Antapani Lama No. 12', 'affected' => 37, 'status' => 'Selesai'],
            ['code' => 'LP-2026-0017', 'date' => '12 Okt 2026', 'address' => 'Jl. Purwakarta Blok C',     'affected' => 25, 'status' => 'Selesai'],
        ];

        $notifications = [
            ['title' => 'LP-2026-0048 diverifikasi', 'message' => 'BPBD memverifikasi pengiriman bantuan ke wilayah Anda.', 'time' => '30 Okt, 19.40', 'isUnread' => true],
            ['title' => 'LP-2026-0041 diterima',     'message' => 'Laporan masuk antrean dan menunggu penetapan BPBD.',    'time' => '27 Okt, 10.02', 'isUnread' => true],
            ['title' => 'LP-2026-0029 selesai',      'message' => 'Penanganan bantuan air bersih telah selesai.',           'time' => '21 Okt, 16.25', 'isUnread' => false],
        ];

        return view('rt.dashboard', compact('stats', 'latestReport', 'reports', 'notifications'));
    }
}
