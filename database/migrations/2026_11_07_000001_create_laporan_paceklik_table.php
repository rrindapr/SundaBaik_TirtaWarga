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
            $table->string('id_laporan', 20)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('alamat', 255);
            $table->unsignedInteger('jumlah_terdampak');
            $table->string('status', 20)->default('menunggu')->index();
            $table->timestamp('tanggal_laporan')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_paceklik');
    }
};
