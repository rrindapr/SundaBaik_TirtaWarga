<?php

namespace App\Http\Controllers\Bpbd;

use App\Http\Controllers\Controller;
use App\Models\LaporanPaceklik;
use Illuminate\View\View;

//PBI-16: peta dasar | PBI-17: titik lokasi laporan di peta
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

        $laporan = LaporanPaceklik::with('penetapanAktif')->get();

        // PBI-17: hanya laporan yang punya koordinat yang bisa digambar sebagai titik
        $titikLaporan = $laporan
            ->filter(fn ($l) => $l->latitude !== null && $l->longitude !== null)
            ->map(fn ($l) => [
                'id'         => $l->id,
                'kode'       => $l->kode,
                'alamat'     => $l->alamat,
                'kk'         => (int) $l->jumlah_kk_terdampak,
                'tingkat'    => $l->tingkat_keparahan ?? 'Sedang',
                'status'     => LaporanPaceklik::STATUS_LABELS[$l->status] ?? 'Tidak diketahui',
                'ditetapkan' => $l->penetapanAktif !== null,
                'lat'        => (float) $l->latitude,
                'lng'        => (float) $l->longitude,
            ])
            ->values();

        $totalLaporan   = $laporan->count();
        $tanpaKoordinat = $totalLaporan - $titikLaporan->count();
        $urlLaporan     = route('bpbd.laporan.index');

        return view('bpbd.peta.index', compact(
            'petaConfig', 'titikLaporan', 'totalLaporan', 'tanpaKoordinat', 'urlLaporan'
        ));
    }
}