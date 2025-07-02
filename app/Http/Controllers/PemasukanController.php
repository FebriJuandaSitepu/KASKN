<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;

class PemasukanController extends Controller
{
    public function create()
    {
        return view('pembayaran.pemasukan.create');
    }

    public function store(Request $request)
    {
        Pembayaran::create([
            'user_id' => auth()->id(),
            'tipe' => 'pemasukan',
            'jumlah' => $request->jumlah,
            'metode' => $request->metode,
            'tanggal' => $request->tanggal,
            'status' => 'valid',
        ]);

        return redirect('/dashboard')->with('success', 'Pemasukan berhasil ditambahkan.');
    }
}
