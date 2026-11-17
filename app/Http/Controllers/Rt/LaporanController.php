<?php

namespace App\Http\Controllers\Rt;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaporanRequest;
use App\Models\LaporanPaceklik;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
        /**
     * PBI-01: tampilkan form isian alamat dan jumlah terdampak.
     * Penyimpanan ditangani store() (PBI-10), validasi oleh StoreLaporanRequest (PBI-02).
     */
    public function create()
    {
        return view('rt.laporan.create');
    }
    /**
     * PBI-04: daftar riwayat laporan milik Ketua RT yang sedang login.
     * Hanya laporan miliknya sendiri yang ditampilkan, terbaru di atas.
     */
    public function index(Request $request)
    {
        $reports = $request->user()
            ->laporan()
            ->latest()
            ->paginate(10);

        return view('rt.laporan.index', compact('reports'));
    }

    //Ketua RT hanya boleh membuka laporan miliknya sendiri
    public function show(Request $request, LaporanPaceklik $laporan)
    {
        abort_unless((int) $laporan->user_id === (int) $request->user()->id, 404);

        return view('rt.laporan.show', ['report' => $laporan]);
    }

    /**
     * PBI-10: simpan laporan baru.
     * PBI-08: kode laporan dibuat otomatis oleh model (lihat trait GeneratesKodeLaporan).
     * PBI-03: setelah tersimpan, tampilkan konfirmasi berhasil beserta ID laporan
     *         (partials/laporan-flash). Kalau gagal simpan, kembali ke form dengan isian
     *         yang dipertahankan supaya bisa dikirim ulang.
     */
     public function store(StoreLaporanRequest $request)
    {
        try {
            // Kalau dua laporan masuk bersamaan dan kodenya kembar, database menolak yang kedua
            // (kolom kode unique); kita ulang sehingga laporan itu mendapat kode berikutnya.
            $laporan = retry(
                3,
                fn () => DB::transaction(fn () => $request->user()->laporan()->create([
                    ...$request->validated(),
                    'status' => 1,
                ])),
                50,
                fn ($e) => $e instanceof UniqueConstraintViolationException
            );
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('rt.laporan.create')
                ->withInput()
                ->with('laporan_gagal', 'Laporan gagal dikirim karena ada gangguan pada sistem. Isian Anda masih tersimpan, silakan coba kirim ulang.');
        }

        return redirect()->route('rt.dashboard')
            ->with('success', "Laporan {$laporan->kode} terkirim.")
            ->with('laporan_terkirim', [
                'kode'         => $laporan->kode,
                'status'       => (int) $laporan->status,
                'status_label' => LaporanPaceklik::STATUS_LABELS[$laporan->status] ?? 'Menunggu',
                'tanggal'      => $laporan->tanggal_lapor->format('d/m/Y H.i'),
                'jumlah_kk'    => $laporan->jumlah_kk_terdampak,
                'alamat'       => $laporan->alamat,
            ]);
    }
}
