<?php

namespace App\Models\Concerns;

use App\Models\LogStatusLaporan;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PBI-34: menyimpan setiap pembuatan dan perubahan status laporan ke tabel log_status_laporan.
 *
 * - Laporan baru dicatat dengan status_lama = null.
 * - Perubahan kolom status dicatat dengan status lama dan status baru.
 * - Perubahan kolom lain (alamat, jumlah KK, dll.) tidak menambah catatan.
 * - Pemanggil bisa mengisi $keteranganStatus sebelum save(); nilainya dipakai sekali lalu dikosongkan.
 */
trait CatatLogStatusLaporan
{
    /** Keterangan untuk pencatatan berikutnya (opsional). */
    public ?string $keteranganStatus = null;

    public static function bootCatatLogStatusLaporan(): void
    {
        static::created(function ($laporan) {
            $laporan->tulisLogStatus(
                null,
                (int) $laporan->status,
                $laporan->keteranganStatus ?? 'Laporan dikirim'
            );
        });

        static::updated(function ($laporan) {
            if (! $laporan->wasChanged('status')) {
                return;
            }

            $laporan->tulisLogStatus(
                (int) $laporan->getOriginal('status'),
                (int) $laporan->status,
                $laporan->keteranganStatus
            );
        });
    }

    public function logStatus(): HasMany
    {
        return $this->hasMany(LogStatusLaporan::class, 'laporan_id')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function tulisLogStatus(?int $statusLama, int $statusBaru, ?string $keterangan): LogStatusLaporan
    {
        $log = LogStatusLaporan::create([
            'laporan_id'  => $this->id,
            'status_lama' => $statusLama,
            'status_baru' => $statusBaru,
            'keterangan'  => $keterangan,
            'petugas'     => auth()->user()?->name,
        ]);

        // Keterangan hanya berlaku untuk satu perubahan, jangan terbawa ke perubahan berikutnya.
        $this->keteranganStatus = null;

        return $log;
    }
}