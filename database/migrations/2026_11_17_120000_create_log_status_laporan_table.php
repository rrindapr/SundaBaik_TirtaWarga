<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PBI-34: riwayat perubahan status setiap laporan
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_status_laporan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporan_paceklik')->cascadeOnDelete();
            $table->unsignedTinyInteger('status_lama')->nullable();   // null untuk pencatatan status awal
            $table->unsignedTinyInteger('status_baru');               // 1 Menunggu, 2 Diverifikasi, 3 Selesai
            $table->text('keterangan')->nullable();
            $table->string('petugas', 100)->nullable();               // nama akun yang mengubah status
            $table->timestamps();                                     // created_at = waktu perubahan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_status_laporan');
    }
};