<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LaporanPaceklik extends Model
{
    use HasFactory;

    protected $table = 'laporan_paceklik';

    protected $fillable = [
        'kode_laporan',
        'nama_rt',
        'nomor_rt',
        'nomor_rw',
        'kelurahan',
        'kecamatan',
        'jumlah_kk_terdampak',
        'tingkat_keparahan',
        'deskripsi_kondisi',
        'status',
    ];

    public function penetapanBantuan(): HasMany
    {
        return $this->hasMany(PenetapanBantuan::class, 'laporan_id');
    }

    public function penetapanAktif(): HasOne
    {
        return $this->hasOne(PenetapanBantuan::class, 'laporan_id')->where('status_bantuan', 'Aktif');
    }
}