<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('laporan_paceklik')) {
            Schema::table('laporan_paceklik', function (Blueprint $table) {
                if (!Schema::hasColumn('laporan_paceklik', 'tingkat_keparahan')) {
                    $table->string('tingkat_keparahan', 20)->default('Sedang')->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('laporan_paceklik')) {
            Schema::table('laporan_paceklik', function (Blueprint $table) {
                if (Schema::hasColumn('laporan_paceklik', 'tingkat_keparahan')) {
                    $table->dropColumn('tingkat_keparahan');
                }
            });
        }
    }
};