<?php

namespace App\Http\Controllers\Rt;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaporanRequest;
use App\Models\LaporanPaceklik;
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

    /**
     * PBI-05: detail satu laporan.
     * Ketua RT hanya boleh membuka laporan miliknya sendiri.
     * Laporan milik RT lain dianggap tidak ada (404).
     */
    public function show(Request $request, LaporanPaceklik $laporan)
    {
        abort_unless((int) $laporan->user_id === (int) $request->user()->id, 404);

        return view('rt.laporan.show', ['report' => $laporan]);
    }

    public function store(StoreLaporanRequest $request)
    {
        $laporan = DB::transaction(function () use ($request) {
            $nomor = LaporanPaceklik::whereYear('created_at', now()->year)
                ->lockForUpdate()->count() + 1;

            return $request->user()->laporan()->create([
                ...$request->validated(),
                'kode'   => sprintf('LP-%d-%04d', now()->year, $nomor),
                'status' => 1,
            ]);
        });

        return redirect()->route('rt.dashboard')
            ->with('success', "Laporan {$laporan->kode} terkirim.");
    }
}