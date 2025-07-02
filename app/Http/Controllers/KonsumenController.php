<?php

namespace App\Http\Controllers;

use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KonsumenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Menampilkan semua data konsumen
    public function index()
    {
        $konsumens = Konsumen::all();
        return view('konsumen.index', compact('konsumens'));
    }

    // Menampilkan form tambah konsumen
    public function create()
    {
        return view('konsumen.form', [
            'konsumen' => new Konsumen()
        ]);
    }

    // Menyimpan data konsumen baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:konsumens,email',
            'no_telepon' => 'nullable|string|max:15',
            'password' => 'required|string|min:6',
        ]);

        $kode = 'KNS' . str_pad(Konsumen::count() + 1, 3, '0', STR_PAD_LEFT);

        Konsumen::create([
            'no_identitas' => $kode,
            'nama' => $request->nama,
            'email' => $request->email,
            'no_telepon' => $request->no_telepon,
            'saldo' => 0,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('konsumen.index')->with('success', 'Konsumen berhasil ditambahkan.');
    }

    // Menampilkan form edit konsumen
    public function edit($id)
    {
        $konsumen = Konsumen::findOrFail($id);
        return view('konsumen.form', compact('konsumen'));
    }

    // Update data konsumen
    public function update(Request $request, $id)
    {
        $konsumen = Konsumen::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:konsumens,email,' . $konsumen->id,
            'no_telepon' => 'nullable|string|max:15',
        ]);

        $konsumen->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_telepon' => $request->no_telepon,
        ]);

        return redirect()->route('konsumen.index')->with('success', 'Konsumen berhasil diupdate.');
    }

    // Hapus konsumen
    public function destroy($id)
    {
        $konsumen = Konsumen::findOrFail($id);
        $konsumen->delete();

        return redirect()->route('konsumen.index')->with('success', 'Konsumen berhasil dihapus.');
    }

    // Reset password konsumen ke default
    public function resetPassword($id)
    {
        $konsumen = Konsumen::findOrFail($id);
        $konsumen->password = Hash::make('password123');
        $konsumen->save();

        return back()->with('success', 'Password konsumen berhasil direset ke default.');
    }
}
