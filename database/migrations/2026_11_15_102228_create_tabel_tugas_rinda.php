<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Laporan Krisis Air dari RT (PBI-13)
        if (!Schema::hasTable('laporan_paceklik')) {
            Schema::create('laporan_paceklik', function (Blueprint $table) {
                $table->id();
                $table->string('kode_laporan', 20)->unique();
                $table->string('nama_rt', 100);
                $table->string('nomor_rt', 5);
                $table->string('nomor_rw', 5);
                $table->string('kelurahan', 100);
                $table->string('kecamatan', 100);
                $table->integer('jumlah_kk_terdampak')->default(0);
                $table->enum('tingkat_keparahan', ['Rendah', 'Sedang', 'Kritis'])->default('Sedang');
                $table->text('deskripsi_kondisi')->nullable();
                $table->enum('status', ['Menunggu', 'Ditetapkan', 'Selesai', 'Ditolak'])->default('Menunggu');
                $table->timestamps();
            });
        }

        // 2. Tabel Penetapan Wilayah Bantuan oleh BPBD (PBI-20)
        if (!Schema::hasTable('penetapan_bantuan')) {
            Schema::create('penetapan_bantuan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('laporan_id')->constrained('laporan_paceklik')->onDelete('restrict')->onUpdate('cascade');
                $table->string('nomor_surat_tugas', 50)->unique();
                $table->string('petugas_bpbd', 100);
                $table->integer('alokasi_tangki_liter')->default(5000);
                $table->date('jadwal_distribusi');
                $table->text('catatan_verifikasi')->nullable();
                $table->enum('status_bantuan', ['Aktif', 'Dalam Pengiriman', 'Selesai', 'Dibatalkan'])->default('Aktif');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('penetapan_bantuan');
        Schema::dropIfExists('laporan_paceklik');
    }
};