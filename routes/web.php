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
