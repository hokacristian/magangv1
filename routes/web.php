<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DirekturController;
use App\Http\Controllers\KatimController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PengeluaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Login dan Logout Routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Middleware untuk role: direktur
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':direktur'])->group(function () {
    Route::get('/direktur/dashboard', [DirekturController::class, 'index'])->name('direktur.dashboard');

});

// Middleware untuk role: direktur
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':direktur'])->group(function () {
    Route::get('/direktur/dashboard', [DirekturController::class, 'index'])->name('direktur.dashboard');
    Route::get('/direktur/download-pdf', [DirekturController::class, 'downloadPDF'])
        ->name('direktur.download-pdf');
});

// Middleware untuk role: katim
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':katim'])->group(function () {
    Route::get('/katim/dashboard', [KatimController::class, 'index'])->name('katim.dashboard');
});

// Middleware untuk role: penerimaan
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':penerimaan'])->group(function () {
    Route::get('/penerimaan/dashboard', [PenerimaanController::class, 'index'])->name('penerimaan.dashboard');
    Route::post('/penerimaan/store', [PenerimaanController::class, 'store'])->name('penerimaan.store');
    Route::post('/penerimaan/update-status/{id}', [PenerimaanController::class, 'updateStatus'])->name('penerimaan.updateStatus'); // Tambahkan route ini untuk update status

});

// AJAX untuk Saldo Rekening
Route::get('/rekening/saldo/{id}', function ($id) {
    $rekening = App\Models\Rekening::findOrFail($id);
    return response()->json(['saldo_saat_ini' => $rekening->saldo_saat_ini]);
})->middleware(['auth']);

// Middleware untuk role: pengeluaran
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':pengeluaran'])->group(function () {
    Route::get('/pengeluaran/dashboard', [PengeluaranController::class, 'index'])->name('pengeluaran.dashboard');
    Route::post('/pengeluaran/store', [PengeluaranController::class, 'store'])->name('pengeluaran.store'); // Tambahkan rute ini untuk pengeluaran
    Route::post('/pengeluaran/update-status/{id}', [PengeluaranController::class, 'updateStatus'])->name('pengeluaran.updateStatus'); // Tambahkan rute ini untuk update status pengeluaran
});


require __DIR__ . '/auth.php';
