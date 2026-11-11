<?php

namespace App\Http\Controllers\Rt;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaporanRequest;
use App\Models\LaporanPaceklik;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
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