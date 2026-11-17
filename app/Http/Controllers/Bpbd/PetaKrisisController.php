<?php

namespace App\Http\Controllers\Bpbd;

use App\Http\Controllers\Controller;
use App\Models\LaporanPaceklik;
use Illuminate\View\View;

//PBI-16: peta dasar untuk memantau wilayah krisis air (dashboard BPBD)
class PetaKrisisController extends Controller
{
    // Pusat peta: Kota Bandung
    public const PUSAT_PETA = [-6.9175, 107.6191];
    public const ZOOM_AWAL  = 12;

    public function index(): View
    {
        $petaConfig = [
            'center'  => self::PUSAT_PETA,
            'zoom'    => self::ZOOM_AWAL,
            'minZoom' => 10,
            'maxZoom' => 18,
            // batas geser peta: kira-kira wilayah Bandung Raya
            'bounds'  => [[-7.25, 107.35], [-6.70, 107.90]],
        ];

        $totalLaporan = LaporanPaceklik::count();

        return view('bpbd.peta.index', compact('petaConfig', 'totalLaporan'));
    }
}