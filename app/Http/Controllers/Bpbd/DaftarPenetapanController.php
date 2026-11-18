<?php

namespace App\Http\Controllers\Bpbd;

use App\Http\Controllers\Controller;
use App\Models\LaporanPaceklik;
use App\Models\PenetapanBantuan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

// PBI-22: daftar wilayah (daerah) yang sudah ditetapkan BPBD sebagai penerima bantuan.
class DaftarPenetapanController extends Controller
{
    public const STATUS_BANTUAN = ['Aktif', 'Dalam Pengiriman', 'Selesai', 'Dibatalkan'];

    public function index(Request $request): View
    {
        // Filter status: hanya nilai yang dikenal yang dipakai, selain itu dianggap "Semua".
        $statusDipilih = $request->query('status');
        if (! in_array($statusDipilih, self::STATUS_BANTUAN, true)) {
            $statusDipilih = null;
        }

        // Pencarian berdasarkan nomor surat tugas, ID laporan, atau alamat wilayah.
        $cari = trim((string) $request->query('cari', ''));

        $daftarPenetapan = PenetapanBantuan::with('laporan.user')
            ->when($statusDipilih, fn (Builder $q) => $q->where('status_bantuan', $statusDipilih))
            ->when($cari !== '', function (Builder $q) use ($cari) {
                $q->where(function (Builder $q) use ($cari) {
                    $q->where('nomor_surat_tugas', 'like', "%{$cari}%")
                        ->orWhereHas('laporan', function (Builder $laporan) use ($cari) {
                            $laporan->where('kode', 'like', "%{$cari}%")
                                ->orWhere('alamat', 'like', "%{$cari}%");
                        });
                });
            })
            // Penetapan terbaru di atas (tanggal penetapan = created_at).
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // Jumlah penetapan per status untuk tab filter.
        $jumlahPerStatus = PenetapanBantuan::query()
            ->selectRaw('status_bantuan, COUNT(*) as total')
            ->groupBy('status_bantuan')
            ->pluck('total', 'status_bantuan');

        // Ringkasan hanya menghitung penetapan yang tidak dibatalkan.
        $penetapanBerlaku = PenetapanBantuan::where('status_bantuan', '!=', 'Dibatalkan');

        $ringkasan = [
            'wilayah'     => (clone $penetapanBerlaku)->distinct()->count('laporan_id'),
            'kk'          => (int) LaporanPaceklik::whereIn('id', (clone $penetapanBerlaku)->select('laporan_id'))
                                ->sum('jumlah_kk_terdampak'),
            'liter'       => (int) (clone $penetapanBerlaku)->sum('alokasi_tangki_liter'),
        ];

        return view('bpbd.penetapan.index', [
            'daftarPenetapan' => $daftarPenetapan,
            'jumlahPerStatus' => $jumlahPerStatus,
            'totalSemua'      => $jumlahPerStatus->sum(),
            'ringkasan'       => $ringkasan,
            'statusDipilih'   => $statusDipilih,
            'cari'            => $cari,
            'daftarStatus'    => self::STATUS_BANTUAN,
        ]);
    }
}