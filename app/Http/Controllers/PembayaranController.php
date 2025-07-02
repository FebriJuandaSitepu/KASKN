<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Menampilkan daftar pembayaran dengan filter
     */
    public function index(Request $request)
    {
        $query = Pembayaran::with('konsumen');

        // Filter berdasarkan search (nama konsumen)
        if ($request->filled('search')) {
            $query->whereHas('konsumen', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tipe
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        $pembayaran = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('pembayaran.index', compact('pembayaran'));
    }

    /**
     * Menampilkan form create dengan parameter tipe opsional
     */
    public function create($tipe = null)
    {
        $konsumens = Konsumen::all();

        // Validasi tipe jika diberikan
        if ($tipe && !in_array($tipe, ['pemasukan', 'pengeluaran'])) {
            abort(404, 'Tipe pembayaran tidak valid.');
        }

        return view('pembayaran.create', compact('konsumens', 'tipe'));
    }

    /**
     * Menyimpan pembayaran baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'konsumen_id' => 'required|exists:konsumens,no_identitas',
            'tipe' => 'required|in:pemasukan,pengeluaran',
            'jumlah' => 'required|numeric|min:0',
            'metode' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'bukti' => 'nullable|image|max:2048',
        ]);

        try {
            $buktiPath = null;
            if ($request->hasFile('bukti')) {
                $buktiPath = $request->file('bukti')->store('bukti_pembayaran', 'public');
            }

            Pembayaran::create([
                'konsumen_id' => $validated['konsumen_id'],
                'tipe' => $validated['tipe'],
                'jumlah' => $validated['jumlah'],
                'metode' => $validated['metode'],
                'tanggal' => $validated['tanggal'],
                'status' => 'valid', // Default status
                'bukti' => $buktiPath,
            ]);

            return redirect()->route('pembayaran.index')
                ->with('success', 'Data pembayaran berhasil disimpan.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menyimpan pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail pembayaran
     */
    public function show($id)
    {
        $pembayaran = Pembayaran::with('konsumen')->findOrFail($id);
        return view('pembayaran.show', compact('pembayaran'));
    }

    /**
     * Menampilkan form edit pembayaran
     */
    public function edit($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $konsumens = Konsumen::all();

        return view('pembayaran.edit', compact('pembayaran', 'konsumens'));
    }

    /**
     * Update pembayaran
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'jumlah' => 'required|numeric|min:0',
            'metode' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'tipe' => 'required|in:pemasukan,pengeluaran',
            'bukti' => 'nullable|image|max:2048',
        ]);

        try {
            $pembayaran = Pembayaran::findOrFail($id);

            // Handle file upload bukti baru
            if ($request->hasFile('bukti')) {
                // Hapus bukti lama jika ada
                if ($pembayaran->bukti && Storage::disk('public')->exists($pembayaran->bukti)) {
                    Storage::disk('public')->delete($pembayaran->bukti);
                }

                $buktiBaru = $request->file('bukti')->store('bukti_pembayaran', 'public');
                $pembayaran->bukti = $buktiBaru;
            }

            // Update data lainnya
            $pembayaran->update([
                'jumlah' => $validated['jumlah'],
                'metode' => $validated['metode'],
                'tanggal' => $validated['tanggal'],
                'tipe' => $validated['tipe'],
            ]);

            return redirect()->route('pembayaran.index')
                ->with('success', 'Data pembayaran berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Update status pembayaran
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:valid,pending,ditolak'
        ]);

        try {
            $pembayaran = Pembayaran::findOrFail($id);
            $pembayaran->status = $request->status;
            $pembayaran->save();

            return redirect()->route('pembayaran.index')
                ->with('success', 'Status pembayaran berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Hapus pembayaran
     */
    public function destroy($id)
    {
        try {
            $pembayaran = Pembayaran::findOrFail($id);

            // Hapus file bukti jika ada
            if ($pembayaran->bukti && Storage::disk('public')->exists($pembayaran->bukti)) {
                Storage::disk('public')->delete($pembayaran->bukti);
            }

            $pembayaran->delete();

            return redirect()->route('pembayaran.index')
                ->with('success', 'Pembayaran berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Export pembayaran ke Excel/PDF (placeholder)
     */
    public function export(Request $request)
    {
        // TODO: Implementasi export dengan Laravel Excel
        return back()->with('info', 'Fitur export sedang dalam pengembangan');
    }

    /**
     * Statistik pembayaran untuk dashboard
     */
    public function statistik()
    {
        $stats = [
            'total_pembayaran' => Pembayaran::count(),
            'total_pemasukan' => Pembayaran::where('tipe', 'pemasukan')->where('status', 'valid')->sum('jumlah'),
            'total_pengeluaran' => Pembayaran::where('tipe', 'pengeluaran')->where('status', 'valid')->sum('jumlah'),
            'pending_count' => Pembayaran::where('status', 'pending')->count(),
        ];

        $stats['saldo_bersih'] = $stats['total_pemasukan'] - $stats['total_pengeluaran'];

        return response()->json([
            'status' => true,
            'data' => $stats
        ]);
    }
}