<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanPaceklik extends Model
{
    protected $table = 'laporan_paceklik';

    protected $fillable = [
        'kode', 'user_id', 'alamat', 'jumlah_kk_terdampak', 'keterangan', 'status',
    ];

    protected function casts(): array
    {
        return [
            'diverifikasi_at' => 'datetime',
            'selesai_at'      => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}