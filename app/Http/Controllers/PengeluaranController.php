<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\Rekening;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        // Filter default
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun', date('Y')); // Default tahun saat ini
        $status = $request->get('status', 'Sudah Disahkan');

        // Ambil data rekening
        $rekenings = Rekening::all();

        // Ambil semua data pengeluaran
        $pengeluarans = Pengeluaran::with('rekening')->orderBy('tanggal', 'desc')->get();

        // Data berdasarkan filter
        $filteredData = Pengeluaran::with('rekening');
        
        if ($bulan) {
            $filteredData = $filteredData->where('bulan', $bulan);
        }
        
        if ($tahun) {
            $filteredData = $filteredData->where('tahun', $tahun);
        }
        
        if ($status) {
            $filteredData = $filteredData->where('status', $status);
        }
        
        $filteredData = $filteredData->get();

        // Filter "Belum Disahkan"
        $belumDisahkan = Pengeluaran::with('rekening')
            ->where('status', 'Belum Disahkan')
            ->orderBy('tanggal', 'desc')
            ->get();

        // Hitung total pengeluaran
        $totalPengeluaran = $filteredData->sum('jumlah_pengeluaran');

        // Jika request AJAX untuk filter
        if ($request->ajax()) {
            return response()->json([
                'filteredData' => $filteredData->map(function ($item) {
                    return [
                        'tanggal' => $item->tanggal ? date('d-m-Y', strtotime($item->tanggal)) : '-',
                        'bulan' => $item->bulan,
                        'tahun' => $item->tahun,
                        'rekening' => $item->rekening->rekening . ' - ' . $item->rekening->bank,
                        'jumlah_pengeluaran' => $item->jumlah_pengeluaran,
                        'keterangan' => $item->keterangan,
                    ];
                }),
                'totalPengeluaran' => $totalPengeluaran
            ]);
        }

        return view('dashboard.pengeluaran', compact(
            'rekenings', 
            'pengeluarans', 
            'belumDisahkan', 
            'totalPengeluaran'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rekening_id' => 'required|exists:rekenings,id',
            'bulan' => 'required|string',
            'tanggal' => 'required|date',
            'jumlah_pengeluaran' => 'required|numeric|min:0',
            'keterangan' => 'required|string',
            'status' => 'required|string|in:Sudah Disahkan,Belum Disahkan',
        ]);

        $rekening = Rekening::findOrFail($request->rekening_id);
        $tahun = date('Y', strtotime($request->tanggal));

        \DB::transaction(function () use ($request, $rekening, $tahun) {
            $saldo_awal = $rekening->saldo_saat_ini;

            $pengeluaran = Pengeluaran::create([
                'rekening_id' => $request->rekening_id,
                'bulan' => $request->bulan,
                'tanggal' => $request->tanggal,
                'tahun' => $tahun,
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

// Menampilkan form edit (untuk AJAX request)
public function edit($id)
{
    $pengeluaran = Pengeluaran::findOrFail($id);
    return response()->json($pengeluaran);
}

// Update data pengeluaran
public function update(Request $request, $id)
{
    $request->validate([
        'tanggal' => 'required|date',
        'bulan' => 'required|string',
        'tahun' => 'required|numeric',
        'jumlah_pengeluaran' => 'required|numeric|min:0',
        'keterangan' => 'required|string',
        'status' => 'required|string|in:Sudah Disahkan,Belum Disahkan',
    ]);
    
    $pengeluaran = Pengeluaran::findOrFail($id);
    $rekening = Rekening::findOrFail($pengeluaran->rekening_id);
    
    \DB::transaction(function () use ($request, $pengeluaran, $rekening) {
        // Jika status pengeluaran sudah disahkan dan nilai pengeluaran berubah
        if ($pengeluaran->status === 'Sudah Disahkan' && $pengeluaran->jumlah_pengeluaran != $request->jumlah_pengeluaran) {
            // Hitung selisih
            $selisih = $request->jumlah_pengeluaran - $pengeluaran->jumlah_pengeluaran;
            
            // Update saldo rekening
            $rekening->saldo_saat_ini -= $selisih;
            $rekening->pengeluaran += $selisih;
            $rekening->saldo_akhir = $rekening->saldo_saat_ini;
            $rekening->save();
        }
        
        // Hitung saldo akhir baru untuk data pengeluaran
        $saldo_akhir = $pengeluaran->saldo_awal - $request->jumlah_pengeluaran;
        
        // Update data pengeluaran
        $pengeluaran->update([
            'tanggal' => $request->tanggal,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'jumlah_pengeluaran' => $request->jumlah_pengeluaran,
            'saldo_akhir' => $saldo_akhir,
            'keterangan' => $request->keterangan,
            'status' => $request->status,
        ]);
    });
    
    return redirect()->route('pengeluaran.dashboard')->with('success', 'Data pengeluaran berhasil diperbarui!');
}

// Hapus data pengeluaran
public function destroy($id)
{
    $pengeluaran = Pengeluaran::findOrFail($id);
    $rekening = Rekening::findOrFail($pengeluaran->rekening_id);
    
    \DB::transaction(function () use ($pengeluaran, $rekening) {
        // Jika status pengeluaran sudah disahkan, tambahkan saldo rekening (kembalikan)
        if ($pengeluaran->status === 'Sudah Disahkan') {
            // Tambahkan saldo rekening karena pengeluaran dihapus
            $rekening->saldo_saat_ini += $pengeluaran->jumlah_pengeluaran;
            $rekening->pengeluaran -= $pengeluaran->jumlah_pengeluaran;
            $rekening->saldo_akhir = $rekening->saldo_saat_ini;
            $rekening->save();
        }
        
        // Hapus data pengeluaran
        $pengeluaran->delete();
    });
    
    return redirect()->route('pengeluaran.dashboard')->with('success', 'Data pengeluaran berhasil dihapus!');
}

}