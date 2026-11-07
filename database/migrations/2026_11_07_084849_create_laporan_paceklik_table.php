<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('laporan_paceklik', function (Blueprint $table) {
        $table->id('laporan_id');
        $table->foreignId('id_rt')->constrained('users');
        $table->text('alamat_lengkap');
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();
        $table->unsignedInteger('jumlah_terdampak');
        $table->dateTime('tanggal_lapor')->useCurrent();
        $table->enum('status_laporan', ['Menunggu', 'Diverifikasi', 'Selesai'])->default('Menunggu');
    });
}

public function down(): void
{
    Schema::dropIfExists('laporan_paceklik');
}
};
