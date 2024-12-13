<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\KatimController;
use App\Http\Controllers\DirekturController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'role:penerimaan'])->group(function () {
    Route::get('/penerimaan', [PenerimaanController::class, 'index'])->name('penerimaan.index');
});

Route::middleware(['auth', 'role:pengeluaran'])->group(function () {
    Route::get('/pengeluaran', [PengeluaranController::class, 'index'])->name('pengeluaran.index');
});

Route::middleware(['auth', 'role:katim'])->group(function () {
    Route::get('/katim', [KatimController::class, 'index'])->name('katim.index');
});

Route::middleware(['auth', 'role:direktur'])->group(function () {
    Route::get('/direktur', [DirekturController::class, 'index'])->name('direktur.index');
});
