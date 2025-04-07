<?php

namespace App\Http\Controllers;

use App\Models\Penerimaan;
use App\Models\Rekening;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Tambahkan impor ini


class PenerimaanController extends Controller
{
    public function index(Request $request)
{
    // Filter request
    $bulan = $request->get('bulan');
    $tahun = $request->get('tahun', date('Y')); // Default tahun saat ini jika tidak diisi
    $status = $request->get('status', 'Sudah Disahkan'); // Default status "Sudah Disahkan"

    // Ambil semua data rekening
    $rekenings = Rekening::all();

    // Ambil data penerimaan
    $penerimaans = Penerimaan::with('rekening')->orderBy('tanggal', 'desc')->get();

    // Data berdasarkan filter
    $filteredData = Penerimaan::with('rekening');
    
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
    $belumDisahkan = Penerimaan::with('rekening')
        ->where('status', 'Belum Disahkan')
        ->orderBy('tanggal', 'desc')
        ->get();

    // Hitung total pendapatan
    $totalPendapatan = $filteredData->sum('penerimaan');

    // Modifikasi pada bagian ini di controller
if ($request->ajax()) {
    return response()->json([
        'filteredData' => $filteredData->map(function ($item) {
            return [
                'bulan' => $item->bulan,
                'tahun' => $item->tahun,
                'tanggal' => $item->tanggal ? date('d-m-Y', strtotime($item->tanggal)) : '-',
                'rekening' => $item->rekening->rekening . ' - ' . $item->rekening->bank,
                'penerimaan' => $item->penerimaan,
                'keterangan' => $item->keterangan, // Tambahkan ini
            ];
        }),
        'totalPendapatan' => $totalPendapatan
    ]);
}

    // Return ke view
    return view('dashboard.penerimaan', compact(
        'rekenings', 
        'penerimaans', 
        'belumDisahkan', 
        'totalPendapatan'
    ));
}

    public function store(Request $request)
    {
        $request->validate([
            'rekening_id' => 'required|exists:rekenings,id',
            'bulan' => 'required|string',
            'tanggal' => 'required|date',
            'penerimaan' => 'required|numeric|min:0',
            'keterangan' => 'required|string',
            'status' => 'required|string|in:Sudah Disahkan,Belum Disahkan',
        ]);

        $rekening = Rekening::findOrFail($request->rekening_id);
        $tahun = date('Y', strtotime($request->tanggal));

        \DB::transaction(function () use ($request, $rekening, $tahun) {
            $saldo_awal = $rekening->saldo_saat_ini;

            $penerimaan = Penerimaan::create([
                'rekening_id' => $request->rekening_id,
                'bulan' => $request->bulan,
                'tanggal' => $request->tanggal,
                'tahun' => $tahun,
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

// Menampilkan form edit (untuk AJAX request)
public function edit($id)
{
    $penerimaan = Penerimaan::findOrFail($id);
    return response()->json($penerimaan);
}

// Update data penerimaan
public function update(Request $request, $id)
{
    $request->validate([
        'tanggal' => 'required|date',
        'bulan' => 'required|string',
        'tahun' => 'required|numeric',
        'penerimaan' => 'required|numeric|min:0',
        'keterangan' => 'required|string',
    ]);
    
    $penerimaan = Penerimaan::findOrFail($id);
    $rekening = Rekening::findOrFail($penerimaan->rekening_id);
    
    \DB::transaction(function () use ($request, $penerimaan, $rekening) {
        // Jika status penerimaan sudah disahkan dan nilai penerimaan berubah
        if ($penerimaan->status === 'Sudah Disahkan' && $penerimaan->penerimaan != $request->penerimaan) {
            // Hitung selisih
            $selisih = $request->penerimaan - $penerimaan->penerimaan;
            
            // Update saldo rekening
            $rekening->saldo_saat_ini += $selisih;
            $rekening->penerimaan += $selisih;
            $rekening->saldo_akhir = $rekening->saldo_saat_ini;
            $rekening->save();
        }
        
        // Hitung saldo akhir baru untuk data penerimaan
        $saldo_akhir = $penerimaan->saldo_awal + $request->penerimaan;
        
        // Update data penerimaan
        $penerimaan->update([
            'tanggal' => $request->tanggal,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'penerimaan' => $request->penerimaan,
            'saldo_akhir' => $saldo_akhir,
            'keterangan' => $request->keterangan,
        ]);
    });
    
    return redirect()->route('penerimaan.dashboard')->with('success', 'Data penerimaan berhasil diperbarui!');
}

// Hapus data penerimaan
public function destroy($id)
{
    $penerimaan = Penerimaan::findOrFail($id);
    $rekening = Rekening::findOrFail($penerimaan->rekening_id);
    
    DB::transaction(function () use ($penerimaan, $rekening) {
        // Jika status penerimaan sudah disahkan, kurangi saldo rekening
        if ($penerimaan->status === 'Sudah Disahkan') {
            // Kurangi saldo rekening karena penerimaan dihapus
            $rekening->saldo_saat_ini -= $penerimaan->penerimaan;
            $rekening->penerimaan -= $penerimaan->penerimaan;
            $rekening->saldo_akhir = $rekening->saldo_saat_ini;
            $rekening->save();
        }
        
        // Hapus data penerimaan
        $penerimaan->delete();
    });
    
    return redirect()->route('penerimaan.dashboard')->with('success', 'Data penerimaan berhasil dihapus!');
}

}