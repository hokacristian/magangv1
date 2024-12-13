<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KatimController extends Controller
{
    public function index()
    {
        return view('dashboard.katim'); // Tampilkan view dashboard direktur
    }
}
