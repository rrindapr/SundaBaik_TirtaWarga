<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LaporanPaceklik extends Model
{
    use HasFactory;

    public const STATUS_LABELS = [
        1 => 'Menunggu',
        2 => 'Diverifikasi',
        3 => 'Selesai',
    ];

    protected $table = 'laporan_paceklik';

    protected $fillable = [
        'kode',
        'user_id',
        'alamat',
        'jumlah_kk_terdampak',
        'keterangan',
        'status',
        'tingkat_keparahan',
    ];

    protected function casts(): array
    {
        return [
            'status'          => 'integer',
            'diverifikasi_at' => 'datetime',
            'selesai_at'      => 'datetime',
        ];
    }

    //ketua rt mengirim dokumen (pbi 10)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //penetapan bantuan (pbi 20)
    public function penetapanBantuan(): HasMany
    {
        return $this->hasMany(PenetapanBantuan::class, 'laporan_id');
    }

    //penetapan bantuan aktif (pbi 20)
    public function penetapanAktif(): HasOne
    {
        return $this->hasOne(PenetapanBantuan::class, 'laporan_id')->where('status_bantuan', 'Aktif');
    }
}