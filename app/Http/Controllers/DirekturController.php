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
        // Data yang dikirim ke view
        $data = [
            'message' => 'HAIIII',
        ];

        // Generate PDF dari view 'pdf.hai'
        $pdf = PDF::loadView('pdf.hai', $data);

        // Tampilkan file PDF untuk didownload
        return $pdf->download('file_hai.pdf');
    }
}
