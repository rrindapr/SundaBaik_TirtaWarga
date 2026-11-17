<?php

namespace App\Http\Controllers\Rt;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaporanRequest;
use App\Models\LaporanPaceklik;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
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
     */
    public function store(StoreLaporanRequest $request)
    {
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

        return redirect()->route('rt.dashboard')
            ->with('success', "Laporan {$laporan->kode} terkirim.");
    }
}
