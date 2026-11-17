<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenetapanBantuan extends Model
{
    use HasFactory;

    protected $table = 'penetapan_bantuan';

    protected $fillable = [
        'laporan_id',
        'nomor_surat_tugas',
        'petugas_bpbd',
        'alokasi_tangki_liter',
        'jadwal_distribusi',
        'catatan_verifikasi',
        'status_bantuan',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanPaceklik::class, 'laporan_id');
    }
}