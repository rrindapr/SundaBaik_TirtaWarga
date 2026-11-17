<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// PBI-34: tabel log_status_laporan
class LogStatusLaporan extends Model
{
    protected $table = 'log_status_laporan';

    protected $fillable = [
        'laporan_id',
        'status_lama',
        'status_baru',
        'keterangan',
        'petugas',
    ];

    protected function casts(): array
    {
        return [
            'status_lama' => 'integer',
            'status_baru' => 'integer',
        ];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanPaceklik::class, 'laporan_id');
    }
}