<?php

use Illuminate\Support\Facades\Route;

Route::get('/accounts', [\App\Http\Controllers\AccountController::class, 'index'])->name('accounts.index');
Route::get('/accounts/create', [\App\Http\Controllers\AccountController::class, 'create'])->name('accounts.create');
Route::post('/accounts', [\App\Http\Controllers\AccountController::class, 'store'])->name('accounts.store');
Route::get('/accounts/{account}/edit', [\App\Http\Controllers\AccountController::class, 'edit'])->name('accounts.edit');
Route::put('/accounts/{account}', [\App\Http\Controllers\AccountController::class, 'update'])->name('accounts.update');
Route::get('/buku-besar', [\App\Http\Controllers\BukuBesarController::class, 'index'])->name('buku-besar.index');
Route::get('/jurnal', [\App\Http\Controllers\JurnalController::class, 'index'])->name('jurnal.index');
Route::get('/jurnal/create', [\App\Http\Controllers\JurnalController::class, 'create'])->name('jurnal.create');
Route::post('/jurnal', [\App\Http\Controllers\JurnalController::class, 'store'])->name('jurnal.store');
Route::get('/jurnal/{jurnal}', [\App\Http\Controllers\JurnalController::class, 'show'])->name('jurnal.show');
Route::get('/kas', [\App\Http\Controllers\KasController::class, 'index'])->name('kas.index');
Route::get('/kas/create', [\App\Http\Controllers\KasController::class, 'create'])->name('kas.create');
Route::post('/kas', [\App\Http\Controllers\KasController::class, 'store'])->name('kas.store');
Route::get('/kas/{cashTransaction}', [\App\Http\Controllers\KasController::class, 'show'])->name('kas.show');
