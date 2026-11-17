<?php

namespace App\Http\Controllers\Rt;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaporanRequest;
use App\Models\LaporanPaceklik;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PenerimaanLaporanController extends Controller
{
    /**
     * PBI-07: endpoint penerima data laporan.
     * Data dari Ketua RT divalidasi lewat StoreLaporanRequest, lalu disimpan ke laporan_paceklik
     * dan dibalas dalam bentuk JSON.
     * Status dan kode tidak diambil dari input: status awal ditetapkan PBI-09, kode dibuat PBI-08.
     */
    public function store(StoreLaporanRequest $request): JsonResponse
    {
        // Sama seperti PBI-10: kalau kode kembar karena dua kiriman bersamaan, coba lagi.
        $laporan = retry(
            3,
            fn () => DB::transaction(fn () => $request->user()->laporan()->create(
                $request->validated()
            )),
            50,
            fn ($e) => $e instanceof UniqueConstraintViolationException
        );

        $laporan->refresh();

        return response()->json([
            'success' => true,
            'message' => "Laporan {$laporan->kode} berhasil diterima.",
            'data'    => [
                'kode'                => $laporan->kode,
                'alamat'              => $laporan->alamat,
                'jumlah_kk_terdampak' => $laporan->jumlah_kk_terdampak,
                'keterangan'          => $laporan->keterangan,
                'status'              => $laporan->status,
                'status_label'        => LaporanPaceklik::STATUS_LABELS[$laporan->status] ?? 'Tidak diketahui',
                'tanggal_lapor'       => $laporan->tanggal_lapor->toIso8601String(),
            ],
        ], 201);
    }
}