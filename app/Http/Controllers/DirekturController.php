<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF; // Import Facade PDF

class DirekturController extends Controller
{
    public function index()
    {
        return view('dashboard.direktur');
    }

    public function downloadPDF()
{
    // Data tambahan jika diperlukan
    $data = [];

    // Generate PDF dari template view
    $pdf = PDF::loadView('pdf.rekonsiliasi', $data);

    // Unduh PDF
    return $pdf->download('Rekonsiliasi_Saldo_BLU_Maret2023.pdf');
}
}
