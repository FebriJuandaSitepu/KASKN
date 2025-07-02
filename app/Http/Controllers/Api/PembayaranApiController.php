<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PembayaranApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Menampilkan daftar pembayaran
     */
    public function index(Request $request) 
    {
        $query = Pembayaran::with('konsumen');

        // Filter berdasarkan konsumen jika ada
        if ($request->filled('konsumen_id')) {
            $query->where('konsumen_id', $request->konsumen_id);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tipe
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_dari') && $request->filled('tanggal_sampai')) {
            $query->whereBetween('tanggal', [$request->tanggal_dari, $request->tanggal_sampai]);
        }

        $pembayaran = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Daftar pembayaran berhasil diambil',
            'data' => $pembayaran
        ]);
    }

    /**
     * Menyimpan pembayaran baru
     */
    public function store(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'konsumen_id' => 'required|exists:konsumens,no_identitas',
            'tipe' => 'required|in:pemasukan,pengeluaran',
            'jumlah' => 'required|numeric|min:0',
            'metode' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            
            // Upload bukti jika ada
            if ($request->hasFile('bukti')) {
                $data['bukti'] = $request->file('bukti')->store('bukti_pembayaran', 'public');
            }

            // Set status default
            $data['status'] = 'pending';

            $pembayaran = Pembayaran::create($data);

            return response()->json([
                'status' => true,
                'message' => 'Pembayaran berhasil disimpan',
                'data' => $pembayaran->load('konsumen')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail pembayaran
     */
    public function show($id)
    {
        try {
            $pembayaran = Pembayaran::with('konsumen')->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Detail pembayaran berhasil diambil',
                'data' => $pembayaran
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Pembayaran tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update pembayaran
     */
    public function update(Request $request, $id) 
    {
        $validator = Validator::make($request->all(), [
            'tipe' => 'sometimes|in:pemasukan,pengeluaran',
            'jumlah' => 'sometimes|numeric|min:0',
            'metode' => 'sometimes|string|max:100',
            'tanggal' => 'sometimes|date',
            'bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pembayaran = Pembayaran::findOrFail($id);
            $data = $validator->validated();

            // Upload bukti baru jika ada
            if ($request->hasFile('bukti')) {
                // Hapus bukti lama jika ada
                if ($pembayaran->bukti && \Storage::disk('public')->exists($pembayaran->bukti)) {
                    \Storage::disk('public')->delete($pembayaran->bukti);
                }
                $data['bukti'] = $request->file('bukti')->store('bukti_pembayaran', 'public');
            }

            $pembayaran->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Pembayaran berhasil diperbarui',
                'data' => $pembayaran->load('konsumen')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui pembayaran: ' . $e->getMessage()
            ], 500);
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
            if ($pembayaran->bukti && \Storage::disk('public')->exists($pembayaran->bukti)) {
                \Storage::disk('public')->delete($pembayaran->bukti);
            }

            $pembayaran->delete();

            return response()->json([
                'status' => true,
                'message' => 'Pembayaran berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status pembayaran
     */
    public function updateStatus(Request $request, $id) 
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,valid,ditolak'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Status tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pembayaran = Pembayaran::findOrFail($id);
            $pembayaran->status = $request->status;
            $pembayaran->save();

            return response()->json([
                'status' => true,
                'message' => 'Status pembayaran berhasil diperbarui',
                'data' => $pembayaran->load('konsumen')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistik pembayaran
     */
    public function statistik()
    {
        try {
            $totalPembayaran = Pembayaran::count();
            $totalPemasukan = Pembayaran::where('tipe', 'pemasukan')->where('status', 'valid')->sum('jumlah');
            $totalPengeluaran = Pembayaran::where('tipe', 'pengeluaran')->where('status', 'valid')->sum('jumlah');
            $saldoBersih = $totalPemasukan - $totalPengeluaran;
            
            $pending = Pembayaran::where('status', 'pending')->count();
            $valid = Pembayaran::where('status', 'valid')->count();
            $ditolak = Pembayaran::where('status', 'ditolak')->count();

            return response()->json([
                'status' => true,
                'message' => 'Statistik pembayaran berhasil diambil',
                'data' => [
                    'total_pembayaran' => $totalPembayaran,
                    'total_pemasukan' => $totalPemasukan,
                    'total_pengeluaran' => $totalPengeluaran,
                    'saldo_bersih' => $saldoBersih,
                    'status_count' => [
                        'pending' => $pending,
                        'valid' => $valid,
                        'ditolak' => $ditolak
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }
}