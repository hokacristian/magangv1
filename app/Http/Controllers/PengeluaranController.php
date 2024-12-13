<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\Rekening;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function index()
{
    $rekenings = Rekening::all();

    // Ambil data pengeluaran terurut berdasarkan waktu input
    $pengeluarans = Pengeluaran::with('rekening')->orderBy('created_at', 'asc')->get();

    // Filter data berdasarkan status
    $belumDisahkan = $pengeluarans->where('status', 'Belum Disahkan');
    $sudahDisahkan = $pengeluarans->where('status', 'Sudah Disahkan');

    // Hitung saldo akhir secara berurutan
    $saldoCache = [];
    foreach ($pengeluarans as $pengeluaran) {
        $rekeningId = $pengeluaran->rekening_id;

        // Inisialisasi saldo awal jika belum ada
        if (!isset($saldoCache[$rekeningId])) {
            $saldoCache[$rekeningId] = Rekening::find($rekeningId)->saldo_saat_ini;
        }

        // Set saldo awal dari cache
        $pengeluaran->saldo_awal = $saldoCache[$rekeningId];

        // Hitung saldo akhir hanya jika status adalah "Sudah Disahkan"
        if ($pengeluaran->status === 'Sudah Disahkan') {
            $pengeluaran->saldo_akhir = $saldoCache[$rekeningId] - $pengeluaran->jumlah_pengeluaran;
            $saldoCache[$rekeningId] = $pengeluaran->saldo_akhir; // Update saldo akhir ke cache
        } else {
            $pengeluaran->saldo_akhir = $pengeluaran->saldo_awal; // Tidak berubah jika belum disahkan
        }
    }

    return view('dashboard.pengeluaran', compact('rekenings', 'pengeluarans', 'belumDisahkan', 'sudahDisahkan'));
}


    public function store(Request $request)
{
    $rekening = Rekening::findOrFail($request->rekening_id);

    $saldo_awal = $rekening->saldo_saat_ini; // Ambil saldo saat ini

    // Buat data pengeluaran
    $pengeluaran = Pengeluaran::create([
        'rekening_id' => $request->rekening_id,
        'bulan' => $request->bulan,
        'saldo_awal' => $saldo_awal,
        'jumlah_pengeluaran' => $request->jumlah_pengeluaran,
        'keterangan' => $request->keterangan,
        'status' => $request->status,
        'saldo_akhir' => $request->status === 'Sudah Disahkan' ? $saldo_awal - $request->jumlah_pengeluaran : $saldo_awal,
    ]);

    // Perbarui saldo rekening hanya jika status "Sudah Disahkan"
    if ($request->status === 'Sudah Disahkan') {
        $saldo_akhir = $saldo_awal - $request->jumlah_pengeluaran;
        $rekening->update(['saldo_saat_ini' => $saldo_akhir]);
    }

    return redirect()->route('pengeluaran.dashboard')->with('success', 'Data pengeluaran berhasil disimpan!');
}


public function updateStatus(Request $request, $id)
{
    $pengeluaran = Pengeluaran::findOrFail($id);
    $rekening = Rekening::findOrFail($pengeluaran->rekening_id);

    if ($request->status === 'Sudah Disahkan' && $pengeluaran->status === 'Belum Disahkan') {
        // Update saldo rekening
        $saldo_akhir = $rekening->saldo_saat_ini - $pengeluaran->jumlah_pengeluaran;
        $rekening->update(['saldo_saat_ini' => $saldo_akhir]);

        // Perbarui saldo akhir pada pengeluaran
        $pengeluaran->saldo_akhir = $saldo_akhir;
    }

    $pengeluaran->status = $request->status;
    $pengeluaran->save();

    return redirect()->route('pengeluaran.dashboard')->with('success', 'Status berhasil diperbarui!');
}


}
