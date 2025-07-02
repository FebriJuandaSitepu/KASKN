<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    // Kirim notifikasi
    public function kirim(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'pesan' => 'required|string',
            'user_id' => 'nullable|exists:users,id'
        ]);

        if ($request->user_id) {
            Notifikasi::create([
                'user_id' => $request->user_id,
                'judul' => $request->judul,
                'pesan' => $request->pesan
            ]);
        } else {
            $users = User::all();
            foreach ($users as $user) {
                Notifikasi::create([
                    'user_id' => $user->id,
                    'judul' => $request->judul,
                    'pesan' => $request->pesan
                ]);
            }
        }

        return back()->with('success', 'Notifikasi berhasil dikirim.');
    }

    // Tampilkan notifikasi (web)
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $notifikasi = Notifikasi::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('notifikasi.index', compact('notifikasi'));
    }
}
