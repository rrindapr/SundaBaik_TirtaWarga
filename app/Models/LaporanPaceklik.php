<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<<<<<<< Updated upstream
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanPaceklik extends Model
{
    /**
     * Nama tabel yang digunakan model ini.
     */
    protected $table = 'laporan_paceklik';

    /**
     * Konstanta status laporan.
     */
    public const STATUS_MENUNGGU     = 'menunggu';
    public const STATUS_DIVERIFIKASI = 'diverifikasi';
    public const STATUS_SELESAI      = 'selesai';

    /**
     * Daftar status yang valid.
     */
    public const VALID_STATUSES = [
        self::STATUS_MENUNGGU,
        self::STATUS_DIVERIFIKASI,
        self::STATUS_SELESAI,
    ];

    /**
     * Kolom yang boleh diisi secara mass-assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'alamat',
        'jumlah_terdampak',
    ];

    /**
     * Cast atribut ke tipe native.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah_terdampak' => 'integer',
            'tanggal_laporan'  => 'datetime',
        ];
    }

    /**
     * Boot model events.
     *
     * - PBI 8:  Auto-generate id_laporan unik format LP-YYYY-NNNN
     * - PBI 11: Auto-set tanggal_laporan ke waktu server saat ini
     */
    protected static function booted(): void
    {
        static::creating(function (LaporanPaceklik $laporan) {
            // PBI 8 — Generate ID laporan unik otomatis
            $tahun  = now()->year;
            $prefix = "LP-{$tahun}-";

            $lastId = static::where('id_laporan', 'like', "{$prefix}%")
                ->orderByRaw("CAST(SUBSTR(id_laporan, -4) AS INTEGER) DESC")
                ->value('id_laporan');

            $nextNumber = $lastId
                ? ((int) substr($lastId, -4)) + 1
                : 1;

            $laporan->id_laporan = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // PBI 11 — Tanggal laporan otomatis dari server
            $laporan->tanggal_laporan = $laporan->tanggal_laporan ?? now();
        });
    }

    /**
     * Relasi: laporan dimiliki oleh seorang user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
=======

class LaporanPaceklik extends Model
{
    protected $table      = 'laporan_paceklik';
    protected $primaryKey = 'laporan_id';
    public $timestamps    = false;

    protected $fillable = ['id_rt', 'alamat_lengkap', 'latitude', 'longitude', 'jumlah_terdampak'];
>>>>>>> Stashed changes
}
