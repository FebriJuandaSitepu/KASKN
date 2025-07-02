<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topup;
use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TopupApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Menampilkan daftar topup dengan pagination
     */
    public function index()
    {
        $topups = Topup::with('konsumen')
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Daftar topup berhasil diambil',
            'data' => $topups
        ]);
    }

    /**
     * Konfirmasi topup (admin only)
     */
    public function confirm($id)
    {
        try {
            $topup = Topup::findOrFail($id);
            
            if ($topup->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Topup sudah diproses sebelumnya'
                ], 400);
            }

            // Update status topup
            $topup->status = 'diterima';
            $topup->save();

            // Update saldo konsumen
            if ($topup->konsumen) {
                $topup->konsumen->increment('saldo', $topup->nominal);
            }

            return response()->json([
                'status' => true,
                'message' => 'Topup berhasil dikonfirmasi',
                'data' => $topup->load('konsumen')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengkonfirmasi topup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tolak topup (admin only)
     */
    public function reject($id)
    {
        try {
            $topup = Topup::findOrFail($id);
            
            if ($topup->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Topup sudah diproses sebelumnya'
                ], 400);
            }

            $topup->status = 'ditolak';
            $topup->save();

            return response()->json([
                'status' => true,
                'message' => 'Topup berhasil ditolak',
                'data' => $topup->load('konsumen')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menolak topup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Histori topup dengan join ke konsumens (FIXED)
     */
    public function histori()
    {
        $topups = DB::table('topups')
            ->join('konsumens', 'topups.konsumen_id', '=', 'konsumens.no_identitas') // ✅ FIXED: join ke konsumens
            ->select(
                'topups.*', 
                'konsumens.nama as nama_konsumen',
                'konsumens.email as email_konsumen'
            )
            ->orderByDesc('topups.created_at')
            ->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Histori topup berhasil diambil',
            'data' => $topups
        ]);
    }

    /**
     * Topup manual (admin only) - FIXED: pakai Konsumen bukan User
     */
    public function topupManual(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'konsumen_id' => 'required|exists:konsumens,no_identitas',
            'nominal' => 'required|numeric|min:1000|max:10000000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // ✅ FIXED: Pakai Konsumen, bukan User
            $konsumen = Konsumen::where('no_identitas', $request->konsumen_id)->firstOrFail();
            
            // Update saldo konsumen
            $konsumen->saldo += $request->nominal;
            $konsumen->save();

            // Buat record topup
            $topup = Topup::create([
                'konsumen_id' => $konsumen->no_identitas,
                'nominal' => $request->nominal,
                'status' => 'diterima', // Langsung diterima karena manual
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Topup manual berhasil ditambahkan',
                'data' => [
                    'topup' => $topup,
                    'konsumen' => $konsumen,
                    'saldo_baru' => $konsumen->saldo
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal melakukan topup manual: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifikasi QR untuk konsumen
     */
    public function verifikasiQR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'konsumen_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Konsumen ID diperlukan',
                'errors' => $validator->errors()
            ], 422);
        }

        $konsumen = Konsumen::where('no_identitas', $request->konsumen_id)->first();

        if (!$konsumen) {
            return response()->json([
                'status' => false,
                'message' => 'Konsumen tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Konsumen ditemukan',
            'data' => $konsumen
        ]);
    }

    /**
     * Store topup dari Flutter
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'konsumen_id' => 'required|exists:konsumens,no_identitas',
            'nominal' => 'required|numeric|min:1000|max:10000000',
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Upload bukti transfer
            $buktiPath = $request->file('bukti_transfer')->store('bukti_transfer', 'public');

            // Buat record topup
            $topup = Topup::create([
                'konsumen_id' => $request->konsumen_id,
                'nominal' => $request->nominal,
                'bukti_transfer' => $buktiPath,
                'status' => 'pending',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Permintaan topup berhasil dikirim. Menunggu konfirmasi admin.',
                'data' => $topup->load('konsumen')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirim permintaan topup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show detail topup
     */
    public function show($id)
    {
        try {
            $topup = Topup::with('konsumen')->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Detail topup berhasil diambil',
                'data' => $topup
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Topup tidak ditemukan'
            ], 404);
        }
    }
}