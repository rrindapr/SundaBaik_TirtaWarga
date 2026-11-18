<?php

namespace App\Http\Controllers\Bpbd;

use App\Http\Controllers\Controller;
use App\Models\PenetapanBantuan;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

// PBI-23: detail wilayah terpilih (penerima bantuan) yang dibuka dari daftar penetapan (PBI-22).
class DetailPenetapanController extends Controller
{
    public function show(PenetapanBantuan $penetapan): View
    {
        $penetapan->load(['laporan.user', 'laporan.logStatus']);

        $laporan = $penetapan->laporan;

        // Riwayat penetapan lain untuk wilayah (laporan) yang sama, misalnya penetapan lama yang dibatalkan.
        $riwayatPenetapan = PenetapanBantuan::where('laporan_id', $penetapan->laporan_id)
            ->whereKeyNot($penetapan->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        // Titik lokasi hanya ditampilkan kalau laporan punya koordinat (PBI-17).
        $koordinat = ($laporan && $laporan->latitude !== null && $laporan->longitude !== null)
            ? ['lat' => (float) $laporan->latitude, 'lng' => (float) $laporan->longitude]
            : null;

        // Sisa hari menuju jadwal distribusi (negatif = sudah lewat).
        $jadwal = Carbon::parse($penetapan->jadwal_distribusi)->startOfDay();
        $selisihHari = (int) now()->startOfDay()->diffInDays($jadwal, false);

        return view('bpbd.penetapan.show', [
            'penetapan'        => $penetapan,
            'laporan'          => $laporan,
            'riwayatPenetapan' => $riwayatPenetapan,
            'koordinat'        => $koordinat,
            'jadwal'           => $jadwal,
            'selisihHari'      => $selisihHari,
        ]);
    }
}