<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\KatimController;
use App\Http\Controllers\DirekturController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PengeluaranController;
use Illuminate\Support\Facades\Route;

// Login dan Logout Routes
Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');



// Middleware untuk role: direktur
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':direktur'])->group(function () {
    Route::get('/direktur/dashboard', [DirekturController::class, 'index'])->name('direktur.dashboard');
    Route::post('/direktur/rekonsiliasi', [DirekturController::class, 'filterRekonsiliasi'])->name('direktur.rekonsiliasi');
    Route::post('/direktur/preview-pdf', [DirekturController::class, 'previewPDF'])->name('direktur.preview-pdf');
    Route::post('/direktur/download-pdf', [DirekturController::class, 'downloadPDF'])->name('direktur.download-pdf');

});


// Middleware untuk role: katim
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':katim'])->group(function () {
    Route::get('/katim/dashboard', [KatimController::class, 'index'])->name('katim.dashboard');
    Route::match(['get', 'post'], '/katim/rekonsiliasi', [KatimController::class, 'filterRekonsiliasi'])->name('katim.rekonsiliasi');
    Route::get('/katim/transaksi-detail', [KatimController::class, 'getTransaksiDetail'])->name('katim.getTransaksiDetail');
    Route::post('/katim/preview-pdf', [KatimController::class, 'previewPDF'])->name('katim.preview-pdf');
    Route::post('/katim/download-pdf', [KatimController::class, 'downloadPDF'])->name('katim.download-pdf');
});

// Middleware untuk role: penerimaan
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':penerimaan'])->group(function () {
    Route::get('/penerimaan/dashboard', [PenerimaanController::class, 'index'])->name('penerimaan.dashboard');
    Route::post('/penerimaan/store', [PenerimaanController::class, 'store'])->name('penerimaan.store');
    Route::post('/penerimaan/update-status/{id}', [PenerimaanController::class, 'updateStatus'])->name('penerimaan.updateStatus'); // Tambahkan route ini untuk update status
    Route::get('/penerimaan/{id}/edit', [PenerimaanController::class, 'edit'])->name('penerimaan.edit');
    Route::put('/penerimaan/{id}', [PenerimaanController::class, 'update'])->name('penerimaan.update');
    Route::delete('/penerimaan/{id}', [PenerimaanController::class, 'destroy'])->name('penerimaan.destroy');
});

// AJAX untuk Saldo Rekening
Route::get('/rekening/saldo/{id}', function ($id) {
    $rekening = App\Models\Rekening::findOrFail($id);
    return response()->json(['saldo_saat_ini' => $rekening->saldo_saat_ini]);
})->middleware(['auth']);

// Middleware untuk role: pengeluaran
Route::middleware(['auth', App\Http\Middleware\RoleMiddleware::class . ':pengeluaran'])->group(function () {
    Route::get('/pengeluaran/dashboard', [PengeluaranController::class, 'index'])->name('pengeluaran.dashboard');
    Route::post('/pengeluaran/store', [PengeluaranController::class, 'store'])->name('pengeluaran.store');
    Route::post('/pengeluaran/update-status/{id}', [PengeluaranController::class, 'updateStatus'])->name('pengeluaran.updateStatus');
    Route::get('/pengeluaran/{id}/edit', [PengeluaranController::class, 'edit'])->name('pengeluaran.edit');
    Route::put('/pengeluaran/{id}', [PengeluaranController::class, 'update'])->name('pengeluaran.update');
    Route::delete('/pengeluaran/{id}', [PengeluaranController::class, 'destroy'])->name('pengeluaran.destroy');
});


require __DIR__ . '/auth.php';
