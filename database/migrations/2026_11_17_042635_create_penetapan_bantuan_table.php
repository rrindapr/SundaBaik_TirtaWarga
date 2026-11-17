<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//tabel penetapan wilayah penerima bantuan oleh BPBD
return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('penetapan_bantuan');
    }
};