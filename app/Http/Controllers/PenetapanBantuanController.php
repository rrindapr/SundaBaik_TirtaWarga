<?php

namespace App\Http\Controllers;

use App\Models\LaporanPaceklik;
use App\Models\PenetapanBantuan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class PenetapanBantuanController extends Controller
{
    // Menampilkan Dashboard Tabel Laporan (PBI-13)
    public function index(): View
    {
        $daftarLaporan = LaporanPaceklik::with('penetapanAktif')
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

        $petugasAktif = 'Marpaung Rinda Puspa Rani';

        return view('bpbd.dashboard', compact('daftarLaporan', 'petugasAktif'));
    }

    // Menyimpan Penetapan Bantuan dengan Logika Sequence Diagram (PBI-20)
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'laporan_id' => 'required|integer|exists:laporan_paceklik,id',
            'petugas_bpbd' => 'required|string|max:100',
            'alokasi_tangki_liter' => 'required|integer|min:1000',
            'jadwal_distribusi' => 'required|date|after_or_equal:today',
            'catatan_verifikasi' => 'nullable|string|max:1000',
        ]);

        $laporan = LaporanPaceklik::find($validated['laporan_id']);

        // LOGIKA PENOLAKAN: Tolak jika sudah Ditetapkan atau sudah ada bantuan berstatus Aktif
        $existingBantuanAktif = PenetapanBantuan::where('laporan_id', $laporan->id)
            ->where('status_bantuan', 'Aktif')
            ->first();

        if ($laporan->status === 'Ditetapkan' || $existingBantuanAktif !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan ditolak! Wilayah RT ini sudah berstatus Aktif sebagai penerima bantuan.',
                'kode_laporan' => $laporan->kode_laporan
            ], 422);
        }

        // Buat Nomor Surat Tugas otomatis
        $nomorSurat = 'ST/BPBD/' . date('Ymd') . '/' . strtoupper(substr(uniqid(), -5));

        try {
            DB::beginTransaction();

            $penetapan = PenetapanBantuan::create([
                'laporan_id' => $laporan->id,
                'nomor_surat_tugas' => $nomorSurat,
                'petugas_bpbd' => $validated['petugas_bpbd'],
                'alokasi_tangki_liter' => $validated['alokasi_tangki_liter'],
                'jadwal_distribusi' => $validated['jadwal_distribusi'],
                'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
                'status_bantuan' => 'Aktif',
            ]);

            // Ubah status laporan jadi 'Ditetapkan'
            $laporan->update([
                'status' => 'Ditetapkan'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wilayah berhasil ditetapkan sebagai penerima bantuan tangki air bersih.',
                'data' => [
                    'laporan_id' => $laporan->id,
                    'kode_laporan' => $laporan->kode_laporan,
                    'nomor_surat_tugas' => $penetapan->nomor_surat_tugas,
                    'status_baru' => 'Ditetapkan'
                ]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }
}