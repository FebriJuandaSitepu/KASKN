<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function showLoginForm()
    {
        Session::forget('errors');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Autentikasi berhasil, redirect ke halam home
            return redirect()->intended('dashboard');
        } else {
            // Autentikadi gagal, kembali ke halaman login dengan pesan error
            return redirect()->back()->withErrors(['email' => 'Invalid credentials']);
        }
    }

    public function showHome()
    {
        return view('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function ShowPass()
    {
        return view('auth.forgot-password');
    }

    public function showInformasi()
    {
        return view('informasi');
    }

    public function showPembayaran()
    {
        return view('pembayaran');
    }

    public function showPemesanan()
    {
        return view('pemesanan');
    }

    public function showLapangan()
    {
        return view('lapangan.index');
    }

    public function showKonsumen()
    {
        return view('konsumen');
    }
}
