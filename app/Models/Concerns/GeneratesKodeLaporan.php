<?php

namespace App\Models\Concerns;

/**
 * PBI-08: pemberian ID (kode) laporan yang unik, format LP-2026-0001.
 *
 * Dipakai oleh model LaporanPaceklik. Setiap laporan baru otomatis mendapat kode,
 * dari jalur mana pun laporan itu dibuat. Kode yang sudah diisi manual
 * (misalnya oleh seeder atau test) tidak ditimpa.
 */
trait GeneratesKodeLaporan
{
    public static function bootGeneratesKodeLaporan(): void
    {
        static::creating(function ($laporan) {
            if (empty($laporan->kode)) {
                $laporan->kode = static::generateKode();
            }
        });
    }

    /**
     * Nomor urut diambil dari kode terbesar pada tahun berjalan (bukan count),
     * jadi tetap aman kalau ada laporan yang dihapus. Nomor mulai lagi dari 0001 tiap tahun.
     * Kolom `kode` yang unique di database menjadi pengaman terakhir.
     */
    public static function generateKode(): string
    {
        $prefix = 'LP-' . now()->year . '-';

        $terakhir = static::where('kode', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('kode')
            ->value('kode');

        $nomor = $terakhir ? ((int) substr($terakhir, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $nomor, 4, '0', STR_PAD_LEFT);
    }
}
