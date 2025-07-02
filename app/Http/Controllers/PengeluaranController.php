<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;

class PengeluaranController extends Controller
{
    public function create()
    {
        return view('pembayaran.pengeluaran.create');
    }

    public function store(Request $request)
    {
        Pembayaran::create([
            'user_id' => auth()->id(),
            'tipe' => 'pengeluaran',
            'jumlah' => $request->jumlah,
            'metode' => $request->metode,
            'tanggal' => $request->tanggal,
            'status' => 'valid',
        ]);

        return redirect('/dashboard')->with('success', 'Pengeluaran berhasil ditambahkan.');
    }
}
