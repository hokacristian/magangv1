<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DirekturController;
use App\Http\Controllers\KatimController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PengeluaranController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':direktur'])->group(function () {
    Route::get('/direktur/dashboard', [DirekturController::class, 'index'])->name('direktur.dashboard');
});

// Middleware untuk role: katim
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':katim'])->group(function () {
    Route::get('/katim/dashboard', [KatimController::class, 'index'])->name('katim.dashboard');
});

// Middleware untuk role: penerimaan
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':penerimaan'])->group(function () {
    Route::get('/penerimaan/dashboard', [PenerimaanController::class, 'index'])->name('penerimaan.dashboard');
});

// Middleware untuk role: pengeluaran
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':pengeluaran'])->group(function () {
    Route::get('/pengeluaran/dashboard', [PengeluaranController::class, 'index'])->name('pengeluaran.dashboard');
});

require __DIR__.'/auth.php';
