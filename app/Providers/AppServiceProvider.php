<?php

namespace App\Providers;

use App\View\Composers\IncomingReportComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // PBI-12: jumlah laporan baru untuk badge menu "Laporan Masuk" BPBD.
        View::composer('layouts.app', IncomingReportComposer::class);
    }
}