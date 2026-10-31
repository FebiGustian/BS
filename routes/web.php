<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TransaksiStokController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::view('/selamat-datang', 'selamat-datang')->name('selamat-datang');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::prefix('stok/{jenis}')
        ->where(['jenis' => 'masuk|keluar', 'id' => '[0-9]+'])
        ->name('stok.')
        ->controller(TransaksiStokController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

    Route::resource('barang', BarangController::class);
    Route::resource('kategori', KategoriController::class)->except('show');
});

Route::middleware('auth')->group(base_path('routes/akuntansi.php'));

Route::middleware('auth')->group(function () {
    Route::get('/penyesuaian', [\App\Http\Controllers\PenyesuaianStokController::class, 'index'])->name('penyesuaian.index');
    Route::get('/penyesuaian/create', [\App\Http\Controllers\PenyesuaianStokController::class, 'create'])->name('penyesuaian.create');
    Route::post('/penyesuaian', [\App\Http\Controllers\PenyesuaianStokController::class, 'store'])->name('penyesuaian.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/activity-logs', [\App\Http\Controllers\LogLoginController::class, 'index'])->name('activity-logs.index');
});
