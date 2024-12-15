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
        $penerimaans = Penerimaan::with('rekening')->orderBy('created_at', 'asc')->get();

        $belumDisahkan = $penerimaans->where('status', 'Belum Disahkan');
        $sudahDisahkan = $penerimaans->where('status', 'Sudah Disahkan');

        return view('dashboard.penerimaan', compact('rekenings', 'penerimaans', 'belumDisahkan', 'sudahDisahkan'));
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
