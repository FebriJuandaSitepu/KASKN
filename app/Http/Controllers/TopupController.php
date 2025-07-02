<?php

namespace App\Http\Controllers;

use App\Models\Topup;
use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TopupController extends Controller
{
    public function index()
    {
        $topups = Topup::with('konsumen')->latest()->get();
        $konsumens = Konsumen::all(); // untuk form input manual

        return view('topups.index', compact('topups', 'konsumens'));
    }

    public function histori()
    {
        $topups = Topup::with('konsumen')->orderByDesc('created_at')->paginate(15);
        return view('topup.histori', compact('topups'));
    }

    public function verifikasiQR(Request $request)
    {
        $id = $request->input('konsumen_id');
        $konsumen = Konsumen::where('no_identitas', $id)->first();

        if (!$konsumen) {
            return back()->with('error', 'Konsumen tidak ditemukan.');
        }

        return view('topup.verifikasi', compact('konsumen'));
    }

    public function topupManual(Request $request)
    {
        $request->validate([
            'konsumen_id' => 'required|exists:konsumens,no_identitas',
            'nominal' => 'required|numeric|min:1000',
        ]);

        $konsumen = Konsumen::where('no_identitas', $request->konsumen_id)->first();
        $konsumen->saldo += $request->nominal;
        $konsumen->save();

        Topup::create([
            'konsumen_id' => $konsumen->no_identitas,
            'nominal' => $request->nominal,
            'status' => 'diterima', // status valid untuk ditampilkan
        ]);

        return back()->with('success', 'Topup berhasil ditambahkan secara manual.');
    }

    public function konfirmasi($id)
    {
        $topup = Topup::with('konsumen')->findOrFail($id);
        $topup->status = 'diterima';
        $topup->save();

        if ($topup->konsumen) {
            $topup->konsumen->increment('saldo', $topup->nominal);
        }

        return redirect()->route('admin.topup')->with('success', 'Topup berhasil dikonfirmasi.');
    }

    public function storeFlutter(Request $request)
    {
        $request->validate([
            'konsumen_id' => 'required|exists:konsumens,no_identitas',
            'nominal' => 'required|numeric',
            'bukti_transfer' => 'required|image|max:2048'
        ]);

        $path = $request->file('bukti_transfer')->store('bukti_transfer', 'public');

        Topup::create([
            'konsumen_id' => $request->konsumen_id,
            'nominal' => $request->nominal,
            'bukti_transfer' => $path,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Berhasil dikirim. Menunggu konfirmasi admin.']);
    }

    public function show($id)
    {
        $topup = Topup::with('konsumen')->findOrFail($id);
        return view('topup.show', compact('topup'));
    }
}
