<?php

namespace App\Http\Controllers;

use App\Models\LaporanPaceklik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LaporanPaceklikController extends Controller
{
    /**
     * Tampilkan daftar laporan milik user yang sedang login.
     */
    public function index(Request $request)
    {
        $laporans = LaporanPaceklik::where('user_id', $request->user()->id)
            ->orderByDesc('tanggal_laporan')
            ->get();

        return view('laporan-paceklik.index', compact('laporans'));
    }

    /**
     * Simpan laporan paceklik baru ke database.
     *
     * PBI 10 — Data tersimpan aman dan terstruktur di database.
     * PBI 8  — id_laporan di-generate otomatis oleh Model.
     * PBI 11 — tanggal_laporan diisi otomatis oleh Model.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'alamat'            => ['required', 'string', 'max:255'],
                'jumlah_terdampak'  => ['required', 'integer', 'min:1'],
            ],
            [
                'alamat.required'           => 'Alamat wajib diisi.',
                'alamat.max'                => 'Alamat maksimal 255 karakter.',
                'jumlah_terdampak.required' => 'Jumlah terdampak wajib diisi.',
                'jumlah_terdampak.integer'  => 'Jumlah terdampak harus berupa angka.',
                'jumlah_terdampak.min'      => 'Jumlah terdampak minimal 1.',
            ]
        );

        $laporan = LaporanPaceklik::create([
            'user_id'           => $request->user()->id,
            'alamat'            => $validated['alamat'],
            'jumlah_terdampak'  => $validated['jumlah_terdampak'],
        ]);

        return redirect()
            ->back()
            ->with('success', "Laporan {$laporan->id_laporan} berhasil dikirim.");
    }
}
