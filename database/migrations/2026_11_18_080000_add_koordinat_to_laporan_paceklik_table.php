<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PBI-17: koordinat lokasi laporan (latitude, longitude sesuai ERD) untuk titik di peta krisis.
// Nullable: laporan lama dan laporan tanpa titik lokasi tetap valid.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_paceklik', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('alamat');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_paceklik', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};