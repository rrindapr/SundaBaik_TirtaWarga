<?php

namespace App\Http\Controllers\Bpbd;

use App\Http\Controllers\Controller;
use App\Models\LaporanPaceklik;
use App\Models\PenetapanBantuan;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenetapanBantuanController extends Controller
{
    //tabel laporan masuk untuk BPBD
    public function index(Request $request): View
    {
        $daftarLaporan = LaporanPaceklik::with(['user', 'penetapanAktif'])
            ->orderByRaw("
                CASE tingkat_keparahan
                    WHEN 'Kritis' THEN 1
                    WHEN 'Sedang' THEN 2
                    WHEN 'Rendah' THEN 3
                    ELSE 4
                END ASC
            ")
            ->orderBy('created_at', 'desc')
            ->get();

        $petugasAktif = $request->user()->name;

        return view('bpbd.laporan.index', compact('daftarLaporan', 'petugasAktif'));
    }

    //menetapkan wilayah sebagai penerima bantuan (Ditolak jika laporan sudah punya penetapan yang masih aktif)
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'laporan_id'           => 'required|integer|exists:laporan_paceklik,id',
            'alokasi_tangki_liter' => 'required|integer|min:1000',
            'jadwal_distribusi'    => 'required|date|after_or_equal:today',
            'catatan_verifikasi'   => 'nullable|string|max:1000',
        ]);

        $laporan = LaporanPaceklik::findOrFail($validated['laporan_id']);

        $sudahDitetapkan = PenetapanBantuan::where('laporan_id', $laporan->id)
            ->where('status_bantuan', 'Aktif')
            ->exists();

        if ($sudahDitetapkan) {
            return response()->json([
                'success'      => false,
                'message'      => 'Permintaan ditolak! Wilayah RT ini sudah berstatus Aktif sebagai penerima bantuan.',
                'kode_laporan' => $laporan->kode,
            ], 422);
        }

        $nomorSurat = 'ST/BPBD/' . date('Ymd') . '/' . strtoupper(substr(uniqid(), -5));

        try {
            $penetapan = DB::transaction(function () use ($laporan, $validated, $nomorSurat, $request) {
                return PenetapanBantuan::create([
                    'laporan_id'           => $laporan->id,
                    'nomor_surat_tugas'    => $nomorSurat,
                    // nama petugas diambil dari akun BPBD yang login, bukan dari isian form
                    'petugas_bpbd'         => $request->user()->name,
                    'alokasi_tangki_liter' => $validated['alokasi_tangki_liter'],
                    'jadwal_distribusi'    => $validated['jadwal_distribusi'],
                    'catatan_verifikasi'   => $validated['catatan_verifikasi'] ?? null,
                    'status_bantuan'       => 'Aktif',
                ]);
            });
        } catch (Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan penetapan.',
            ], 500);
        }

        // perubahan status
        return response()->json([
            'success' => true,
            'message' => 'Wilayah berhasil ditetapkan sebagai penerima bantuan tangki air bersih.',
            'data'    => [
                'laporan_id'        => $laporan->id,
                'kode_laporan'      => $laporan->kode,
                'nomor_surat_tugas' => $penetapan->nomor_surat_tugas,
                'status_baru'       => 'Ditetapkan',
            ],
        ], 201);
    }
}