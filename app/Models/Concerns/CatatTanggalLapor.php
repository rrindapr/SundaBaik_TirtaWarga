<?php

namespace App\Models\Concerns;

/**
 * PBI-11: pencatatan tanggal lapor otomatis.
 *
 * Setiap laporan baru otomatis mendapat tanggal_lapor = waktu saat laporan dibuat,
 * dari jalur mana pun laporan itu dibuat (form, endpoint, seeder, test).
 * Nilai yang sudah diisi eksplisit (misalnya oleh seeder) tidak ditimpa.
 * Kolom ini tidak masuk $fillable, jadi input dari Ketua RT tidak pernah bisa mengisinya.
 * Saat laporan di-update, tanggal_lapor tidak berubah.
 */
trait CatatTanggalLapor
{
    public static function bootCatatTanggalLapor(): void
    {
        static::creating(function ($laporan) {
            if (empty($laporan->tanggal_lapor)) {
                $laporan->tanggal_lapor = now();
            }
        });
    }

    // Cast ditambahkan dari trait supaya method casts() di model tidak perlu diubah.
    public function initializeCatatTanggalLapor(): void
    {
        $this->mergeCasts(['tanggal_lapor' => 'datetime']);
    }
}