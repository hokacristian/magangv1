<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(); // Proses autentikasi user
    
        $request->session()->regenerate(); // Regenerasi sesi
    
        // Ambil role pengguna setelah login
        $role = Auth::user()->role;
    
        // Redirect berdasarkan role
        switch ($role) {
            case 'direktur':
                return redirect()->route('direktur.dashboard');
            case 'katim':
                return redirect()->route('katim.dashboard');
            case 'penerimaan':
                return redirect()->route('penerimaan.dashboard');
            case 'pengeluaran':
                return redirect()->route('pengeluaran.dashboard');
            default:
                Auth::logout(); // Logout jika role tidak valid
                return redirect('/login')->with('error', 'Role not defined.');
        }
    }
    
    

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
