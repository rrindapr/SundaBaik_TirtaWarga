<?php

<<<<<<< Updated upstream
=======
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Bpbd\DashboardController as BpbdDashboardController;
use App\Http\Controllers\LaporanPaceklikController;
use App\Http\Controllers\Rt\DashboardController as RtDashboardController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Auth;
>>>>>>> Stashed changes
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
<<<<<<< Updated upstream
=======

//auth
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

//bpbd (hanya akun dengan role bpbd)
Route::middleware(['auth', EnsureRole::class . ':bpbd'])
    ->prefix('bpbd')
    ->name('bpbd.')
    ->group(function () {
        Route::get('/', [BpbdDashboardController::class, 'index'])->name('dashboard');
    });

//rt (hanya akun dengan role rt)
Route::middleware(['auth', EnsureRole::class . ':rt'])
    ->prefix('rt')
    ->name('rt.')
    ->group(function () {
        Route::get('/', [RtDashboardController::class, 'index'])->name('dashboard');
    });

//laporan paceklik (kedua role bisa akses)
Route::middleware('auth')->group(function () {
    Route::get('/laporan-paceklik', [LaporanPaceklikController::class, 'index'])
        ->name('laporan-paceklik.index');
    Route::post('/laporan-paceklik', [LaporanPaceklikController::class, 'store'])
        ->name('laporan-paceklik.store');
});
>>>>>>> Stashed changes
