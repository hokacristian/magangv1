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
            'bulan' => 'required|string',
            'tahun' => 'required|numeric'
        ]);

        $bulan = $request->bulan;
        $tahun = $request->tahun;

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
                ->where('tahun', $tahun) // Tambah filter tahun
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
                ->where('tahun', $tahun) // Tambah filter tahun
                ->where('status', 'Sudah Disahkan')
                ->sum('penerimaan');

            // Total penerimaan "Belum Disahkan" untuk rekening dan bulan ini (untuk laporan di bawah)
            $penerimaanBelumDisahkan = Penerimaan::where('rekening_id', $rekening->id)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun) // Tambah filter tahun
                ->where('status', 'Belum Disahkan')
                ->sum('penerimaan');

            // Total pengeluaran "Sudah Disahkan"
            $pengeluaranDisahkan = Pengeluaran::where('rekening_id', $rekening->id)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun) // Tambah filter tahun
                ->where('status', 'Sudah Disahkan')
                ->sum('jumlah_pengeluaran');

            // Total pengeluaran "Belum Disahkan"
            $pengeluaranBelumDisahkan = Pengeluaran::where('rekening_id', $rekening->id)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun) // Tambah filter tahun
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
            'tahun' => $tahun,
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

    private function generateRekonsiliasiData($bulan,$tahun)
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
            ->where('tahun', $tahun) // Tambah filter tahun
            ->orderBy('id', 'asc')
            ->get();

        $saldoAwal = $penerimaanBulan->count() > 0 ? $penerimaanBulan->first()->saldo_awal : 0;

        $penerimaanDisahkan = Penerimaan::where('rekening_id', $rekening->id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun) // Tambah filter tahun
            ->where('status', 'Sudah Disahkan')
            ->sum('penerimaan');

        $pengeluaranDisahkan = Pengeluaran::where('rekening_id', $rekening->id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun) // Tambah filter tahun
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
        'tahun' => $tahun,
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
        'bulan' => 'required|string',
        'tahun' => 'required|numeric'
    ]);

    $bulan = $request->bulan;
    $tahun = $request->tahun;
    $tanggalUnduh = date('d-m-Y'); // Tanggal hari ini


    // Ambil data rekonsiliasi
    $data = $this->generateRekonsiliasiData($bulan, $tahun);

    // Tambahkan flag untuk membedakan mode PDF
    $data['isDownload'] = true;

    // Load view ke PDF
    $pdf = PDF::loadView('pdf.rekonsiliasi', $data)->setPaper('a4', 'landscape');

     // Format nama file sesuai permintaan
     $filename = "Laporan_{$bulan}_{$tahun}_Tanggal Unduh_{$tanggalUnduh}.pdf";

     // Unduh PDF
     return $pdf->download($filename);
}



    public function previewPDF(Request $request)
{
    $request->validate([
        'bulan' => 'required|string',
        'tahun' => 'required|numeric'
    ]);

    $bulan = $request->bulan;
    $tahun = $request->tahun;

    // Panggil logika generateRekonsiliasiData untuk mengambil data
    $data = $this->generateRekonsiliasiData($bulan, $tahun);

    // Load view ke PDF
    $pdf = PDF::loadView('pdf.rekonsiliasi', $data);

    // Format nama file untuk preview
    $filename = "Laporan_{$bulan}_{$tahun}_{$tanggalUnduh}.pdf";

    // Return PDF untuk preview di browser
    return $pdf->stream($filename);
}

// Di KatimController.php tambahkan metode berikut
public function getTransaksiDetail(Request $request)
{
    $bulan = $request->get('bulan');
    $tahun = $request->get('tahun', date('Y'));
    
    // Query untuk mendapatkan transaksi penerimaan
    $penerimaans = Penerimaan::with(['rekening'])
        ->where('status', 'Sudah Disahkan')
        ->when($bulan, function ($query) use ($bulan) {
            return $query->where('bulan', $bulan);
        })
        ->when($tahun, function ($query) use ($tahun) {
            return $query->where('tahun', $tahun);
        })
        ->get()
        ->map(function ($item) {
            // Dapatkan deskripsi keterangan berdasarkan kode COA
            $coaDescription = $this->getCoaDescription($item->keterangan);
            
            return [
                'tanggal' => $item->tanggal, // Simpan tanggal dalam format asli untuk pengurutan
                'tanggal_format' => date('d/m/Y', strtotime($item->tanggal)), // Format untuk tampilan
                'rekening' => $item->rekening->rekening . ' - ' . $item->rekening->bank,
                'keterangan' => $coaDescription,
                'jumlah' => (float)$item->penerimaan, // Pastikan jumlah adalah float
                'jenis' => 'penerimaan'
            ];
        });
    
     // Query untuk mendapatkan transaksi pengeluaran
     $pengeluarans = Pengeluaran::with(['rekening'])
     ->where('status', 'Sudah Disahkan')
     ->when($bulan, function ($query) use ($bulan) {
         return $query->where('bulan', $bulan);
     })
     ->when($tahun, function ($query) use ($tahun) {
         return $query->where('tahun', $tahun);
     })
     ->get()
     ->map(function ($item) {
         // Dapatkan deskripsi keterangan berdasarkan kode COA
         $coaDescription = $this->getCoaDescription($item->keterangan);
         
         return [
             'tanggal' => $item->tanggal, // Simpan tanggal dalam format asli untuk pengurutan
             'tanggal_format' => date('d/m/Y', strtotime($item->tanggal)), // Format untuk tampilan
             'rekening' => $item->rekening->rekening . ' - ' . $item->rekening->bank,
             'keterangan' => $coaDescription,
             'jumlah' => (float)$item->jumlah_pengeluaran, // Pastikan jumlah adalah float
             'jenis' => 'pengeluaran'
         ];
     });
    
    // Gabungkan penerimaan dan pengeluaran
    $transaksi = $penerimaans->concat($pengeluarans)
        ->sortBy('tanggal') // Urutkan berdasarkan tanggal
        ->values()
        ->toArray();
    
    // Dapatkan saldo awal
    $rekenings = Rekening::all();
    $saldoAwal = $rekenings->sum('saldo_awal');
    
    // Hitung total
    $totalPenerimaan = $penerimaans->sum('jumlah');
    $totalPengeluaran = $pengeluarans->sum('jumlah');
    
    return response()->json([
        'transaksi' => $transaksi,
        'saldoAwal' => $saldoAwal,
        'totalPenerimaan' => $totalPenerimaan,
        'totalPengeluaran' => $totalPengeluaran
    ]);

     // Gabungkan penerimaan dan pengeluaran
     $transaksi = $penerimaans->concat($pengeluarans)
     ->sortBy('tanggal') // Urutkan berdasarkan tanggal
     ->values()
     ->toArray();
 
 // Dapatkan saldo awal
 $rekenings = Rekening::all();
 $saldoAwal = (float)$rekenings->sum('saldo_awal');
 
 // Hitung total
 $totalPenerimaan = (float)$penerimaans->sum('jumlah');
 $totalPengeluaran = (float)$pengeluarans->sum('jumlah');
 
 return response()->json([
     'transaksi' => $transaksi,
     'saldoAwal' => $saldoAwal,
     'totalPenerimaan' => $totalPenerimaan,
     'totalPengeluaran' => $totalPengeluaran
 ]);
}


// Fungsi helper untuk mendapatkan deskripsi COA
private function getCoaDescription($code)
{
    $coaDictionary = [
        // Kode penerimaan
        "4100" => "Pendapatan Operasional",
        "4101" => "Pendapatan SPP & UKT",
        "4102" => "Pendapatan Registrasi & Her-Registrasi",
        "4103" => "Pendapatan Ujian Kompetensi",
        "4104" => "Pendapatan Wisuda & Ijazah",
        "4105" => "Pendapatan Sertifikasi & Pelatihan",
        "4200" => "Pendapatan Non-Operasional",
        "4201" => "Pendapatan dari Sewa Fasilitas",
        "4202" => "Pendapatan Penelitian & Hibah",
        "4203" => "Pendapatan Workshop & Seminar",
        "4204" => "Pendapatan dari Kegiatan Mahasiswa",
        "4205" => "Pendapatan Bunga Bank",
        "4300" => "Pendapatan Lain-lain",
        "4301" => "Pendapatan dari Donasi & CSR",
        "4302" => "Pendapatan dari Kerjasama Institusi",
        "4303" => "Pendapatan dari Penjualan Barang/Merchandise",
        "4304" => "Pendapatan dari Kegiatan Sosial",
        
        // Kode pengeluaran
        "5100" => "Beban Pegawai & Tenaga Pendidik",
        "5101" => "Gaji Dosen & Tunjangan Sertifikasi",
        "5102" => "Gaji Tenaga Kependidikan",
        "5103" => "Honorarium Dosen Luar Biasa",
        "5104" => "BPJS Kesehatan & Ketenagakerjaan",
        "5105" => "Biaya Pelatihan dan Pengembangan SDM",
        "5200" => "Beban Operasional Pendidikan",
        "5201" => "Pembelian Alat Tulis Kantor (ATK)",
        "5202" => "Biaya Listrik, Air, dan Internet",
        "5203" => "Pemeliharaan Gedung dan Peralatan",
        "5204" => "Biaya Transportasi dan Perjalanan Dinas",
        "5205" => "Biaya Konsumsi dan Rapat",
        "5300" => "Beban Akademik & Penelitian",
        "5301" => "Biaya Praktikum Mahasiswa",
        "5302" => "Biaya Pengadaan Bahan Lab & Simulasi",
        "5303" => "Biaya Penelitian Dosen dan Mahasiswa",
        "5304" => "Biaya Publikasi Ilmiah dan Seminar",
        "5305" => "Biaya Akreditasi & Sertifikasi Program Studi",
        "5400" => "Beban Mahasiswa & Kegiatan Kemahasiswaan",
        "5401" => "Biaya Organisasi Mahasiswa (BEM, HIMA)",
        "5402" => "Biaya Kegiatan UKM & Kesejahteraan Mahasiswa",
        "5403" => "Bantuan & Beasiswa Mahasiswa",
        "5404" => "Biaya Kegiatan Wisuda",
        "5405" => "Bantuan Kesehatan dan Sosial Mahasiswa",
        "5500" => "Beban Lain-lain",
        "5501" => "Biaya Penyusutan Aset",
        "5502" => "Pajak & Retribusi",
        "5503" => "Biaya Pengelolaan Sampah dan Limbah Medis",
        "5504" => "Biaya CSR & Kegiatan Sosial",
        "5505" => "Biaya Lain-lain Tak Terduga"
    ];
    
    return $coaDictionary[$code] ?? $code;
}

}
