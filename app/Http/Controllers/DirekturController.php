<?php

namespace App\Http\Controllers;

use App\Models\Rekening;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use PDF;

class DirekturController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', null); // Bulan dipilih atau default null
    
        // Query Total Penerimaan (hanya "Sudah Disahkan")
$totalPenerimaan = Penerimaan::selectRaw('rekening_id, sum(penerimaan) as total_penerimaan, status')
->where('status', 'Sudah Disahkan')  // Filter hanya yang sudah disahkan
->when($bulan, function ($query, $bulan) {
    return $query->where('bulan', $bulan);
})
->groupBy('rekening_id', 'status')
->with('rekening')
->get();

// Query Total Pengeluaran (hanya "Sudah Disahkan")
$totalPengeluaran = Pengeluaran::selectRaw('rekening_id, sum(jumlah_pengeluaran) as total_pengeluaran, status')
->where('status', 'Sudah Disahkan')  // Filter hanya yang sudah disahkan
->when($bulan, function ($query, $bulan) {
    return $query->where('bulan', $bulan);
})
->groupBy('rekening_id', 'status')
->with('rekening')
->get();

// Grand Total Penerimaan (hanya total penerimaan yang sudah disahkan)
$grandTotalPenerimaan = $totalPenerimaan->sum('total_penerimaan');

// Grand Total Pengeluaran (hanya total pengeluaran yang sudah disahkan)
$grandTotalPengeluaran = $totalPengeluaran->sum('total_pengeluaran');
    
        // Kirim data ke view dashboard
        return view('dashboard.direktur', compact('totalPenerimaan', 'totalPengeluaran', 'grandTotalPenerimaan', 'grandTotalPengeluaran', 'bulan'));
    }
    

    public function filterRekonsiliasi(Request $request)
    {
        $request->validate([
            'bulan' => 'required|string'
        ]);

        $bulan = $request->bulan;

        // Ambil semua rekening
        $rekenings = Rekening::all();

        // Variabel untuk hasil akhir
        $dataRekening = [];

        $totalPenerimaanDisahkan = 0;
        $totalPengeluaranDisahkan = 0;
        $totalPenerimaanBelum = 0;
        $totalPengeluaranBelum = 0;
        $totalSaldoAkhirSemua = 0;

        foreach ($rekenings as $rekening) {
            // Saldo Awal: ambil dari penerimaan untuk rekening dan bulan ini.
            // Asumsi: saldo_awal dapat diambil dari salah satu record penerimaan bulan tersebut.
            $penerimaanBulan = Penerimaan::where('rekening_id', $rekening->id)
                ->where('bulan', $bulan)
                ->orderBy('id', 'asc')
                ->get();

            $saldoAwal = 0;
            if ($penerimaanBulan->count() > 0) {
                // Ambil saldo_awal dari record pertama penerimaan di bulan tersebut.
                $saldoAwal = $penerimaanBulan->first()->saldo_awal;
            }

            // Total penerimaan "Sudah Disahkan" untuk rekening dan bulan ini
            $penerimaanDisahkan = Penerimaan::where('rekening_id', $rekening->id)
                ->where('bulan', $bulan)
                ->where('status', 'Sudah Disahkan')
                ->sum('penerimaan');

            // Total penerimaan "Belum Disahkan" untuk rekening dan bulan ini (untuk laporan di bawah)
            $penerimaanBelumDisahkan = Penerimaan::where('rekening_id', $rekening->id)
                ->where('bulan', $bulan)
                ->where('status', 'Belum Disahkan')
                ->sum('penerimaan');

            // Total pengeluaran "Sudah Disahkan"
            $pengeluaranDisahkan = Pengeluaran::where('rekening_id', $rekening->id)
                ->where('bulan', $bulan)
                ->where('status', 'Sudah Disahkan')
                ->sum('jumlah_pengeluaran');

            // Total pengeluaran "Belum Disahkan"
            $pengeluaranBelumDisahkan = Pengeluaran::where('rekening_id', $rekening->id)
                ->where('bulan', $bulan)
                ->where('status', 'Belum Disahkan')
                ->sum('jumlah_pengeluaran');

            // Hitung Saldo Akhir per rekening
            $saldoAkhir = $saldoAwal + $penerimaanDisahkan - $pengeluaranDisahkan;

            // Tambahkan ke data total
            $totalPenerimaanDisahkan += $penerimaanDisahkan;
            $totalPengeluaranDisahkan += $pengeluaranDisahkan;
            $totalPenerimaanBelum += $penerimaanBelumDisahkan;
            $totalPengeluaranBelum += $pengeluaranBelumDisahkan;
            $totalSaldoAkhirSemua += $saldoAkhir;

            $dataRekening[] = [
                'rekening' => $rekening->rekening,
                'bank' => $rekening->bank,
                'saldo_awal' => $saldoAwal,
                'penerimaan' => $penerimaanDisahkan,
                'pengeluaran' => $pengeluaranDisahkan,
                'saldo_akhir' => $saldoAkhir,
            ];
        }

        // Hitungan akhir:
        // Saldo Akhir Rekening BLU: $totalSaldoAkhirSemua
        // Pengesahan Pendapatan: $totalPenerimaanDisahkan
        // Pengesahan Belanja: $totalPengeluaranDisahkan
        // Belum Pengesahan: total dari (Penerimaan belum + Pengeluaran belum)
        $belumPengesahan = $totalPenerimaanBelum + $totalPengeluaranBelum;

        // Data yang akan dikirim ke view/pdf
        $data = [
            'bulan' => $bulan,
            'dataRekening' => $dataRekening,
            'saldoAkhirBLU' => $totalSaldoAkhirSemua,
            'pengesahanPendapatan' => $totalPenerimaanDisahkan,
            'pengesahanBelanja' => $totalPengeluaranDisahkan,
            'belumPengesahan' => $belumPengesahan,
            'belumPengesahanPendapatan' => $totalPenerimaanBelum,
            'belumPengesahanBelanja' => $totalPengeluaranBelum,
        ];

        // Tampilkan di view rekonsiliasi
        return view('pdf.rekonsiliasi', $data);
    }

    private function generateRekonsiliasiData($bulan)
{
    // Ambil semua rekening
    $rekenings = Rekening::all();

    // Variabel untuk hasil akhir
    $dataRekening = [];
    $totalPenerimaanDisahkan = 0;
    $totalPengeluaranDisahkan = 0;
    $totalPenerimaanBelum = 0;
    $totalPengeluaranBelum = 0;
    $totalSaldoAkhirSemua = 0;

    foreach ($rekenings as $rekening) {
        $penerimaanBulan = Penerimaan::where('rekening_id', $rekening->id)
            ->where('bulan', $bulan)
            ->orderBy('id', 'asc')
            ->get();

        $saldoAwal = $penerimaanBulan->count() > 0 ? $penerimaanBulan->first()->saldo_awal : 0;

        $penerimaanDisahkan = Penerimaan::where('rekening_id', $rekening->id)
            ->where('bulan', $bulan)
            ->where('status', 'Sudah Disahkan')
            ->sum('penerimaan');

        $pengeluaranDisahkan = Pengeluaran::where('rekening_id', $rekening->id)
            ->where('bulan', $bulan)
            ->where('status', 'Sudah Disahkan')
            ->sum('jumlah_pengeluaran');

        $saldoAkhir = $saldoAwal + $penerimaanDisahkan - $pengeluaranDisahkan;

        $totalPenerimaanDisahkan += $penerimaanDisahkan;
        $totalPengeluaranDisahkan += $pengeluaranDisahkan;
        $totalSaldoAkhirSemua += $saldoAkhir;

        $dataRekening[] = [
            'rekening' => $rekening->rekening,
            'bank' => $rekening->bank,
            'saldo_awal' => $saldoAwal,
            'penerimaan' => $penerimaanDisahkan,
            'pengeluaran' => $pengeluaranDisahkan,
            'saldo_akhir' => $saldoAkhir,
        ];
    }

    $belumPengesahan = $totalPenerimaanBelum + $totalPengeluaranBelum;

    return [
        'bulan' => $bulan,
        'dataRekening' => $dataRekening,
        'saldoAkhirBLU' => $totalSaldoAkhirSemua,
        'pengesahanPendapatan' => $totalPenerimaanDisahkan,
        'pengesahanBelanja' => $totalPengeluaranDisahkan,
        'belumPengesahan' => $belumPengesahan,
        'belumPengesahanPendapatan' => $totalPenerimaanBelum,
        'belumPengesahanBelanja' => $totalPengeluaranBelum,
    ];
}


public function downloadPDF(Request $request)
{
    $request->validate([
        'bulan' => 'required|string'
    ]);

    $bulan = $request->bulan;

    // Ambil data rekonsiliasi
    $data = $this->generateRekonsiliasiData($bulan);

    // Tambahkan flag untuk membedakan mode PDF
    $data['isDownload'] = true;

    // Load view ke PDF
    $pdf = PDF::loadView('pdf.rekonsiliasi', $data)->setPaper('a4', 'landscape');

    // Unduh PDF
    return $pdf->download("Rekonsiliasi_Saldo_BLU_$bulan.pdf");
}



    public function previewPDF(Request $request)
{
    $request->validate([
        'bulan' => 'required|string'
    ]);

    $bulan = $request->bulan;

    // Panggil logika generateRekonsiliasiData untuk mengambil data
    $data = $this->generateRekonsiliasiData($bulan);

    // Load view ke PDF
    $pdf = PDF::loadView('pdf.rekonsiliasi', $data);

    // Return PDF untuk preview di browser
    return $pdf->stream("Rekonsiliasi_Saldo_BLU_$bulan.pdf");
}
}
