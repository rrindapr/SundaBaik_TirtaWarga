<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// PBI-11: tanggal lapor otomatis (kolom tanggal_lapor sesuai ERD)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_paceklik', function (Blueprint $table) {
            $table->dateTime('tanggal_lapor')->nullable()->after('keterangan');
        });

        // Laporan yang sudah ada: tanggal lapor = waktu laporan dibuat.
        DB::table('laporan_paceklik')
            ->whereNull('tanggal_lapor')
            ->update(['tanggal_lapor' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('laporan_paceklik', function (Blueprint $table) {
            $table->dropColumn('tanggal_lapor');
        });
    }
};