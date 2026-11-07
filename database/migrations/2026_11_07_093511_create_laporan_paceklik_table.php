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
        $table->id();
        $table->string('kode', 20)->unique();              // LP-2026-0048
        $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // RT pelapor
        $table->string('alamat');
        $table->unsignedInteger('jumlah_kk_terdampak');
        $table->text('keterangan')->nullable();
        $table->unsignedTinyInteger('status')->default(1)->index(); // 1 Menunggu, 2 Diverifikasi, 3 Selesai
        $table->timestamp('diverifikasi_at')->nullable();
        $table->timestamp('selesai_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('laporan_paceklik');
}
};
