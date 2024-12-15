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
        $pengeluarans = Pengeluaran::with('rekening')->orderBy('created_at', 'asc')->get();

        $belumDisahkan = $pengeluarans->where('status', 'Belum Disahkan');
        $sudahDisahkan = $pengeluarans->where('status', 'Sudah Disahkan');

        return view('dashboard.pengeluaran', compact('rekenings', 'pengeluarans', 'belumDisahkan', 'sudahDisahkan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rekening_id' => 'required|exists:rekenings,id',
            'bulan' => 'required|string',
            'jumlah_pengeluaran' => 'required|numeric|min:0',
            'keterangan' => 'required|string',
            'status' => 'required|string|in:Sudah Disahkan,Belum Disahkan',
        ]);

        $rekening = Rekening::findOrFail($request->rekening_id);

        \DB::transaction(function () use ($request, $rekening) {
            $saldo_awal = $rekening->saldo_saat_ini;

            $pengeluaran = Pengeluaran::create([
                'rekening_id' => $request->rekening_id,
                'bulan' => $request->bulan,
                'saldo_awal' => $saldo_awal,
                'jumlah_pengeluaran' => $request->jumlah_pengeluaran,
                'saldo_akhir' => $saldo_awal - $request->jumlah_pengeluaran,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
            ]);

            if ($request->status === 'Sudah Disahkan') {
                $rekening->kurangiSaldo($request->jumlah_pengeluaran);
            }
        });

        return redirect()->route('pengeluaran.dashboard')->with('success', 'Data pengeluaran berhasil disimpan!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:Sudah Disahkan,Belum Disahkan',
        ]);

        $pengeluaran = Pengeluaran::findOrFail($id);
        $rekening = Rekening::findOrFail($pengeluaran->rekening_id);

        \DB::transaction(function () use ($request, $pengeluaran, $rekening) {
            if ($request->status === 'Sudah Disahkan' && $pengeluaran->status === 'Belum Disahkan') {
                $rekening->kurangiSaldo($pengeluaran->jumlah_pengeluaran);
            } elseif ($request->status === 'Belum Disahkan' && $pengeluaran->status === 'Sudah Disahkan') {
                $rekening->tambahSaldo($pengeluaran->jumlah_pengeluaran);
            }

            $pengeluaran->status = $request->status;
            $pengeluaran->save();
        });

        return redirect()->route('pengeluaran.dashboard')->with('success', 'Status berhasil diperbarui!');
    }
}
