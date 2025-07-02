<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Konsumen;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class LaporanApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('isadmin'); // Admin only
    }

    /**
     * Menampilkan rekap total transaksi per bulan untuk tiap konsumen (FIXED)
     */
    public function rekapTransaksi(): JsonResponse
    {
        try {
            // ✅ FIXED: Ambil konsumen, bukan user
            $konsumens = Konsumen::select('no_identitas', 'nama')->get();

            // ✅ FIXED: Ambil rekap berdasarkan konsumen_id, bukan user_id + GROUP BY yang benar
            $rekap = DB::table('pembayaran')
                ->select(
                    'konsumen_id',
                    DB::raw('MONTH(created_at) as bulan'),
                    DB::raw('YEAR(created_at) as tahun'),
                    DB::raw('SUM(CASE WHEN tipe = "pemasukan" THEN jumlah ELSE 0 END) as total_pemasukan'),
                    DB::raw('SUM(CASE WHEN tipe = "pengeluaran" THEN jumlah ELSE 0 END) as total_pengeluaran'),
                    DB::raw('SUM(CASE WHEN tipe = "pemasukan" THEN jumlah ELSE -jumlah END) as total_bersih')
                )
                ->where('status', 'valid') // Hanya yang sudah valid
                ->whereYear('created_at', date('Y')) // Tahun sekarang
                ->groupByRaw('konsumen_id, MONTH(created_at), YEAR(created_at)') // ✅ FIXED: groupByRaw
                ->get();

            // Format hasil rekap ke dalam bentuk array per konsumen
            $data = $konsumens->map(function ($konsumen) use ($rekap) {
                $konsumenRekap = $rekap->where('konsumen_id', $konsumen->no_identitas);

                $bulan = [];
                for ($i = 1; $i <= 12; $i++) {
                    $dataBulan = $konsumenRekap->firstWhere('bulan', $i);
                    $bulan[$i] = [
                        'pemasukan' => (float) ($dataBulan->total_pemasukan ?? 0),
                        'pengeluaran' => (float) ($dataBulan->total_pengeluaran ?? 0),
                        'bersih' => (float) ($dataBulan->total_bersih ?? 0),
                    ];
                }

                return [
                    'konsumen_id' => $konsumen->no_identitas,
                    'konsumen_nama' => $konsumen->nama,
                    'transaksi_per_bulan' => $bulan,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Rekap transaksi per konsumen berhasil diambil',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil rekap transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Laporan ringkasan untuk dashboard
     */
    public function ringkasan(): JsonResponse
    {
        try {
            $totalKonsumen = Konsumen::count();
            $totalPembayaran = Pembayaran::count();
            $totalPemasukan = Pembayaran::where('tipe', 'pemasukan')->where('status', 'valid')->sum('jumlah');
            $totalPengeluaran = Pembayaran::where('tipe', 'pengeluaran')->where('status', 'valid')->sum('jumlah');
            $saldoBersih = $totalPemasukan - $totalPengeluaran;

            // Transaksi bulan ini - FIXED GROUP BY
            $bulanIni = Pembayaran::whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->where('status', 'valid')
                ->selectRaw('
                    SUM(CASE WHEN tipe = "pemasukan" THEN jumlah ELSE 0 END) as pemasukan_bulan_ini,
                    SUM(CASE WHEN tipe = "pengeluaran" THEN jumlah ELSE 0 END) as pengeluaran_bulan_ini,
                    COUNT(*) as transaksi_bulan_ini
                ')
                ->first(); // ✅ FIXED: langsung first() tanpa group by karena aggregate semua

            // Top 5 konsumen dengan transaksi terbanyak - FIXED GROUP BY
            $topKonsumen = DB::table('pembayaran')
                ->join('konsumens', 'pembayaran.konsumen_id', '=', 'konsumens.no_identitas')
                ->select(
                    'konsumens.no_identitas',
                    'konsumens.nama',
                    DB::raw('COUNT(*) as total_transaksi'),
                    DB::raw('SUM(pembayaran.jumlah) as total_nilai')
                )
                ->where('pembayaran.status', 'valid')
                ->groupByRaw('konsumens.no_identitas, konsumens.nama') // ✅ FIXED: groupByRaw
                ->orderByDesc('total_transaksi')
                ->limit(5)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Ringkasan laporan berhasil diambil',
                'data' => [
                    'total_konsumen' => $totalKonsumen,
                    'total_pembayaran' => $totalPembayaran,
                    'total_pemasukan' => $totalPemasukan,
                    'total_pengeluaran' => $totalPengeluaran,
                    'saldo_bersih' => $saldoBersih,
                    'bulan_ini' => [
                        'pemasukan' => (float) ($bulanIni->pemasukan_bulan_ini ?? 0),
                        'pengeluaran' => (float) ($bulanIni->pengeluaran_bulan_ini ?? 0),
                        'transaksi' => (int) ($bulanIni->transaksi_bulan_ini ?? 0),
                    ],
                    'top_konsumen' => $topKonsumen
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil ringkasan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Laporan transaksi per periode
     */
    public function laporanPeriode(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'konsumen_id' => 'nullable|exists:konsumens,no_identitas',
            'tipe' => 'nullable|in:pemasukan,pengeluaran'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = Pembayaran::with('konsumen')
                ->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai])
                ->where('status', 'valid');

            // Filter konsumen jika ada
            if ($request->filled('konsumen_id')) {
                $query->where('konsumen_id', $request->konsumen_id);
            }

            // Filter tipe jika ada
            if ($request->filled('tipe')) {
                $query->where('tipe', $request->tipe);
            }

            $pembayaran = $query->orderBy('tanggal', 'desc')->get();

            // Ringkasan periode
            $ringkasan = [
                'total_transaksi' => $pembayaran->count(),
                'total_pemasukan' => $pembayaran->where('tipe', 'pemasukan')->sum('jumlah'),
                'total_pengeluaran' => $pembayaran->where('tipe', 'pengeluaran')->sum('jumlah'),
            ];
            $ringkasan['saldo_bersih'] = $ringkasan['total_pemasukan'] - $ringkasan['total_pengeluaran'];

            return response()->json([
                'status' => true,
                'message' => 'Laporan periode berhasil diambil',
                'data' => [
                    'periode' => [
                        'tanggal_mulai' => $request->tanggal_mulai,
                        'tanggal_selesai' => $request->tanggal_selesai,
                    ],
                    'ringkasan' => $ringkasan,
                    'detail_transaksi' => $pembayaran
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil laporan periode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export laporan ke Excel/CSV (placeholder - bisa dikembangkan)
     */
    public function export(Request $request)
    {
        // TODO: Implementasi export ke Excel menggunakan Laravel Excel
        return response()->json([
            'status' => false,
            'message' => 'Fitur export belum tersedia'
        ], 501);
    }
}