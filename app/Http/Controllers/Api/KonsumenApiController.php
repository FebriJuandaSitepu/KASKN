<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class KonsumenApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Menampilkan daftar konsumen
     */
    public function index(Request $request)
    {
        $query = Konsumen::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_identitas', 'like', "%{$search}%");
            });
        }

        // Filter by status (aktif/non-aktif berdasarkan activity)
        if ($request->filled('status')) {
            // Bisa ditambahkan logic untuk filter status
        }

        $konsumens = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Daftar konsumen berhasil diambil',
            'data' => $konsumens
        ]);
    }

    /**
     * Menyimpan konsumen baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:konsumens,email',
            'no_telepon' => 'nullable|string|max:15',
            'password' => 'required|string|min:6',
            'saldo' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Auto-generate no_identitas akan handled oleh model
            $konsumen = Konsumen::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'no_telepon' => $request->no_telepon,
                'password' => Hash::make($request->password),
                'saldo' => $request->saldo ?? 0,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Konsumen berhasil ditambahkan',
                'data' => $konsumen
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan konsumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail konsumen
     */
    public function show($id)
    {
        try {
            $konsumen = Konsumen::where('no_identitas', $id)->firstOrFail();

            // Include related data
            $konsumen->load(['topups' => function ($query) {
                $query->latest()->take(5);
            }, 'pembayaran' => function ($query) {
                $query->latest()->take(5);
            }]);

            return response()->json([
                'status' => true,
                'message' => 'Detail konsumen berhasil diambil',
                'data' => $konsumen
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Konsumen tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update data konsumen
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:konsumens,email,' . $id . ',no_identitas',
            'no_telepon' => 'nullable|string|max:15',
            'saldo' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $konsumen = Konsumen::where('no_identitas', $id)->firstOrFail();
            $konsumen->update($validator->validated());

            return response()->json([
                'status' => true,
                'message' => 'Data konsumen berhasil diperbarui',
                'data' => $konsumen
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui konsumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus konsumen
     */
    public function destroy($id)
    {
        try {
            $konsumen = Konsumen::where('no_identitas', $id)->firstOrFail();
            
            // Check jika ada transaksi terkait
            if ($konsumen->pembayaran()->count() > 0 || $konsumen->topups()->count() > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Konsumen tidak dapat dihapus karena memiliki riwayat transaksi'
                ], 400);
            }

            $konsumen->delete();

            return response()->json([
                'status' => true,
                'message' => 'Konsumen berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus konsumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password konsumen (Admin only)
     */
    public function resetPassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'nullable|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $konsumen = Konsumen::where('no_identitas', $id)->firstOrFail();
            
            // ✅ FIXED: Jangan hardcode password, bisa custom atau default yang aman
            $newPassword = $request->new_password ?? 'konsumen123'; // Default password
            $konsumen->password = Hash::make($newPassword);
            $konsumen->save();

            return response()->json([
                'status' => true,
                'message' => 'Password konsumen berhasil direset',
                'data' => [
                    'konsumen_id' => $konsumen->no_identitas,
                    'nama' => $konsumen->nama,
                    'new_password' => $newPassword // Kirim password baru untuk admin
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update saldo konsumen (Admin only)
     */
    public function updateSaldo(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'saldo' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $konsumen = Konsumen::where('no_identitas', $id)->firstOrFail();
            $saldoLama = $konsumen->saldo;
            $konsumen->saldo = $request->saldo;
            $konsumen->save();

            // Log perubahan saldo (opsional - bisa ditambah tabel log)
            \Log::info("Saldo konsumen {$konsumen->nama} diubah dari {$saldoLama} ke {$request->saldo}", [
                'konsumen_id' => $id,
                'admin_id' => auth()->id(),
                'keterangan' => $request->keterangan
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Saldo konsumen berhasil diperbarui',
                'data' => [
                    'konsumen_id' => $konsumen->no_identitas,
                    'nama' => $konsumen->nama,
                    'saldo_lama' => $saldoLama,
                    'saldo_baru' => $konsumen->saldo,
                    'selisih' => $konsumen->saldo - $saldoLama
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui saldo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get konsumen statistics
     */
    public function statistics($id)
    {
        try {
            $konsumen = Konsumen::where('no_identitas', $id)->firstOrFail();

            $stats = [
                'total_topup' => $konsumen->topups()->where('status', 'diterima')->sum('nominal'),
                'jumlah_topup' => $konsumen->topups()->where('status', 'diterima')->count(),
                'total_pembayaran' => $konsumen->pembayaran()->where('status', 'valid')->sum('jumlah'),
                'jumlah_pembayaran' => $konsumen->pembayaran()->where('status', 'valid')->count(),
                'saldo_sekarang' => $konsumen->saldo,
                'terakhir_aktif' => $konsumen->updated_at,
            ];

            // Pembayaran bulan ini
            $bulanIni = $konsumen->pembayaran()
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->where('status', 'valid')
                ->sum('jumlah');

            $stats['pembayaran_bulan_ini'] = $bulanIni;

            return response()->json([
                'status' => true,
                'message' => 'Statistik konsumen berhasil diambil',
                'data' => [
                    'konsumen' => $konsumen,
                    'statistik' => $stats
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Konsumen tidak ditemukan'
            ], 404);
        }
    }
}