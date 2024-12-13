<?php

namespace App\Http\Controllers;

use App\Models\Penerimaan;
use App\Models\Rekening;
use Illuminate\Http\Request;

class PenerimaanController extends Controller
{
    public function index()
{
    $rekenings = Rekening::all();

    // Ambil data penerimaan terurut berdasarkan waktu input
    $penerimaans = Penerimaan::with('rekening')->orderBy('created_at', 'asc')->get();

    // Filter data berdasarkan status
    $belumDisahkan = $penerimaans->where('status', 'Belum Disahkan');
    $sudahDisahkan = $penerimaans->where('status', 'Sudah Disahkan');

    // Hitung saldo akhir secara berurutan berdasarkan saldo awal dan penerimaan
    $saldoCache = [];
foreach ($penerimaans as $penerimaan) {
    $rekeningId = $penerimaan->rekening_id;

    // Inisialisasi saldo awal jika belum ada
    if (!isset($saldoCache[$rekeningId])) {
        $saldoCache[$rekeningId] = 0;
    }

    // Set saldo awal dari cache
    $penerimaan->saldo_awal = $saldoCache[$rekeningId];

    // Hitung saldo akhir hanya jika status adalah "Sudah Disahkan"
    if ($penerimaan->status === 'Sudah Disahkan') {
        $penerimaan->saldo_akhir = $saldoCache[$rekeningId] + $penerimaan->penerimaan;
        $saldoCache[$rekeningId] = $penerimaan->saldo_akhir; // Update saldo akhir ke cache
    } else {
        $penerimaan->saldo_akhir = $penerimaan->saldo_awal; // Tidak berubah jika belum disahkan
    }
}


    return view('dashboard.penerimaan', compact('rekenings', 'penerimaans', 'belumDisahkan', 'sudahDisahkan'));
}




public function store(Request $request)
{
    $rekening = Rekening::findOrFail($request->rekening_id);

    $saldo_awal = $rekening->saldo_saat_ini; // Ambil saldo saat ini

    // Buat data penerimaan
    $penerimaan = Penerimaan::create([
        'rekening_id' => $request->rekening_id,
        'bulan' => $request->bulan,
        'saldo_awal' => $saldo_awal,
        'penerimaan' => $request->penerimaan,
        'keterangan' => $request->keterangan,
        'status' => $request->status,
    ]);

    // Perbarui saldo rekening hanya jika status "Sudah Disahkan"
    if ($request->status === 'Sudah Disahkan') {
        $saldo_akhir = $saldo_awal + $request->penerimaan;
        $rekening->update(['saldo_saat_ini' => $saldo_akhir]);
    }

    return redirect()->route('penerimaan.dashboard')->with('success', 'Data penerimaan berhasil disimpan!');
}

public function updateStatus(Request $request, $id)
{
    $penerimaan = Penerimaan::findOrFail($id);
    $rekening = Rekening::findOrFail($penerimaan->rekening_id);

    if ($request->status === 'Sudah Disahkan' && $penerimaan->status === 'Belum Disahkan') {
        // Update saldo rekening
        $saldo_akhir = $rekening->saldo_saat_ini + $penerimaan->penerimaan;
        $rekening->update(['saldo_saat_ini' => $saldo_akhir]);

        // Tambahkan saldo akhir pada penerimaan
        $penerimaan->saldo_akhir = $saldo_akhir;
    }

    $penerimaan->status = $request->status;
    $penerimaan->save();

    return redirect()->route('penerimaan.dashboard')->with('success', 'Status berhasil diperbarui!');
}



}
