<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Bpbd\DashboardController as BpbdDashboardController;
use App\Http\Controllers\Rt\DashboardController as RtDashboardController;
use App\Http\Controllers\Rt\LaporanController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Tidak ada registrasi. Akun BPBD dan Ketua RT dibuat lewat UserSeeder.

Route::get('/', function () {
    return redirect(LoginController::dashboardUrl(Auth::user()) ?? route('login'));
});

// Autentikasi
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// BPBD (hanya akun dengan role bpbd)
Route::middleware(['auth', EnsureRole::class . ':bpbd'])
    ->prefix('bpbd')
    ->name('bpbd.')
    ->group(function () {
        Route::get('/', [BpbdDashboardController::class, 'index'])->name('dashboard');
    });

// RT (hanya akun dengan role rt)
Route::middleware(['auth', EnsureRole::class . ':rt'])
    ->prefix('rt')
    ->name('rt.')
    ->group(function () {
        Route::get('/', [RtDashboardController::class, 'index'])->name('dashboard');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');                // PBI-04
        Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');
        Route::get('/laporan/{laporan:kode}', [LaporanController::class, 'show'])->name('laporan.show');   // PBI-05
    });