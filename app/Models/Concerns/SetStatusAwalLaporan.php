<?php

namespace App\Models\Concerns;

/**
 * PBI-09: penetapan status awal laporan "Menunggu" (kode 1).
 *
 * Laporan yang dibuat tanpa status eksplisit otomatis berstatus Menunggu.
 * Status yang sudah diisi (misalnya oleh seeder atau riwayat test) tidak ditimpa.
 * Input dari form atau endpoint tidak pernah dipakai untuk mengisi status, lihat controller.
 */
trait SetStatusAwalLaporan
{
    public const STATUS_MENUNGGU = 1;

    public static function bootSetStatusAwalLaporan(): void
    {
        static::creating(function ($laporan) {
            if ($laporan->status === null) {
                $laporan->status = static::STATUS_MENUNGGU;
            }
        });
    }
}