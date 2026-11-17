<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//dipakai BPBD untuk mengurutkan laporan masuk (Kritis, Sedang, Rendah)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_paceklik', function (Blueprint $table) {
            $table->string('tingkat_keparahan', 20)->default('Sedang')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_paceklik', function (Blueprint $table) {
            $table->dropColumn('tingkat_keparahan');
        });
    }
};