<?php

namespace App\Http\Controllers;

use App\Models\Penerimaan;
use App\Models\Rekening;
use Illuminate\Http\Request;

class PenerimaanController extends Controller
{
    public function index(Request $request)
    {
        // Filter request
        $bulan = $request->get('bulan', 'Januari');
        $status = $request->get('status', 'Sudah Disahkan');

        // Ambil semua data rekening
        $rekenings = Rekening::all();

        // Ambil data penerimaan
        $penerimaans = Penerimaan::with('rekening')->get();

        // Data berdasarkan filter
        $filteredData = Penerimaan::with('rekening')
            ->where('bulan', $bulan)
            ->where('status', $status)
            ->get();

        // Filter "Belum Disahkan"
        $belumDisahkan = $penerimaans->where('status', 'Belum Disahkan');

        // Hitung total pendapatan
        $totalPendapatan = $filteredData->sum('penerimaan');

        // Jika request AJAX untuk filter
        if ($request->ajax()) {
            return response()->json([
                'filteredData' => $filteredData->map(function ($item) {
                    return [
                        'bulan' => $item->bulan,
                        'rekening' => $item->rekening->rekening . ' - ' . $item->rekening->bank,
                        'penerimaan' => $item->penerimaan,
                    ];
                }),
                'totalPendapatan' => $totalPendapatan
            ]);
        }

        // Return ke view
        return view('dashboard.penerimaan', compact('rekenings', 'penerimaans', 'belumDisahkan', 'totalPendapatan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rekening_id' => 'required|exists:rekenings,id',
            'bulan' => 'required|string',
            'penerimaan' => 'required|numeric|min:0',
            'keterangan' => 'required|string',
            'status' => 'required|string|in:Sudah Disahkan,Belum Disahkan',
        ]);

        $rekening = Rekening::findOrFail($request->rekening_id);

        \DB::transaction(function () use ($request, $rekening) {
            $saldo_awal = $rekening->saldo_saat_ini;

            $penerimaan = Penerimaan::create([
                'rekening_id' => $request->rekening_id,
                'bulan' => $request->bulan,
                'saldo_awal' => $saldo_awal,
                'penerimaan' => $request->penerimaan,
                'saldo_akhir' => $saldo_awal + $request->penerimaan,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
            ]);

            if ($request->status === 'Sudah Disahkan') {
                $rekening->tambahSaldo($request->penerimaan);
            }
        });

        return redirect()->route('penerimaan.dashboard')->with('success', 'Data penerimaan berhasil disimpan!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:Sudah Disahkan,Belum Disahkan',
        ]);

        $penerimaan = Penerimaan::findOrFail($id);
        $rekening = Rekening::findOrFail($penerimaan->rekening_id);

        \DB::transaction(function () use ($request, $penerimaan, $rekening) {
            if ($request->status === 'Sudah Disahkan' && $penerimaan->status === 'Belum Disahkan') {
                $rekening->tambahSaldo($penerimaan->penerimaan);
            } elseif ($request->status === 'Belum Disahkan' && $penerimaan->status === 'Sudah Disahkan') {
                $rekening->kurangiSaldo($penerimaan->penerimaan);
            }

            $penerimaan->status = $request->status;
            $penerimaan->save();
        });

        return redirect()->route('penerimaan.dashboard')->with('success', 'Status berhasil diperbarui!');
    }
}
