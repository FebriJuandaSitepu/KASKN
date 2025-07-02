<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Konsumen;
use App\Models\Topup;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // ✅ FIXED: Variable names sesuai dengan yang dipakai di view
        $totalPengguna = Konsumen::count();
        $totalPembayaran = Pembayaran::count();
        $totalPemasukan = Pembayaran::where('tipe', 'pemasukan')
            ->where('status', 'valid')
            ->sum('jumlah');
        $totalPengeluaran = Pembayaran::where('tipe', 'pengeluaran')
            ->where('status', 'valid')
            ->sum('jumlah');

        // Statistik tambahan
        $saldoBersih = $totalPemasukan - $totalPengeluaran;
        $totalTopupPending = Topup::where('status', 'pending')->count();
        $totalPembayaranPending = Pembayaran::where('status', 'pending')->count();

        // Transaksi terakhir dengan relasi konsumen
        $transaksiTerakhir = Pembayaran::with('konsumen')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Topup terbaru yang pending
        $topupPending = Topup::with('konsumen')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Data untuk chart (transaksi 7 hari terakhir) - FIXED GROUP BY
        $chartData = Pembayaran::selectRaw('DATE(created_at) as tanggal, COUNT(*) as jumlah')
            ->where('created_at', '>=', now()->subDays(7))
            ->where('status', 'valid')
            ->groupByRaw('DATE(created_at)') // ✅ FIXED: gunakan RAW untuk GROUP BY
            ->orderByRaw('DATE(created_at)')
            ->get();

        // Transaksi per bulan (12 bulan terakhir) untuk chart - FIXED GROUP BY
        $transaksiPerBulan = Pembayaran::selectRaw('
                YEAR(created_at) as tahun,
                MONTH(created_at) as bulan,
                SUM(CASE WHEN tipe = "pemasukan" THEN jumlah ELSE 0 END) as pemasukan,
                SUM(CASE WHEN tipe = "pengeluaran" THEN jumlah ELSE 0 END) as pengeluaran
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->where('status', 'valid')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)') // ✅ FIXED: sudah benar
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        return view('admin.index', compact(
            'totalPengguna',        // ✅ Sesuai dengan view: {{ $totalPengguna }}
            'totalPembayaran',      // ✅ Sesuai dengan view: {{ $totalPembayaran }}
            'totalPemasukan',       // ✅ Sesuai dengan view: {{ $totalPemasukan }}
            'totalPengeluaran',     // ✅ Sesuai dengan view: {{ $totalPengeluaran }}
            'saldoBersih',
            'totalTopupPending',
            'totalPembayaranPending',
            'transaksiTerakhir',    // ✅ Sesuai dengan view: @forelse ($transaksiTerakhir as ...)
            'topupPending',
            'chartData',
            'transaksiPerBulan'
        ));
    }

    /**
     * API endpoint untuk dashboard data (untuk mobile/AJAX)
     */
    public function apiData()
    {
        $data = [
            'statistik' => [
                'total_pengguna' => Konsumen::count(),
                'total_pembayaran' => Pembayaran::count(),
                'total_pemasukan' => Pembayaran::where('tipe', 'pemasukan')->where('status', 'valid')->sum('jumlah'),
                'total_pengeluaran' => Pembayaran::where('tipe', 'pengeluaran')->where('status', 'valid')->sum('jumlah'),
                'topup_pending' => Topup::where('status', 'pending')->count(),
                'pembayaran_pending' => Pembayaran::where('status', 'pending')->count(),
            ],
            'transaksi_terakhir' => Pembayaran::with('konsumen')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'topup_pending' => Topup::with('konsumen')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
        ];

        $data['statistik']['saldo_bersih'] = $data['statistik']['total_pemasukan'] - $data['statistik']['total_pengeluaran'];

        return response()->json([
            'status' => true,
            'message' => 'Data dashboard berhasil diambil',
            'data' => $data
        ]);
    }

    /**
     * Get chart data untuk AJAX - FIXED GROUP BY
     */
    public function chartData()
    {
        // Data untuk chart transaksi 30 hari terakhir - FIXED
        $transaksiHarian = Pembayaran::selectRaw('
                DATE(created_at) as tanggal,
                SUM(CASE WHEN tipe = "pemasukan" THEN jumlah ELSE 0 END) as pemasukan,
                SUM(CASE WHEN tipe = "pengeluaran" THEN jumlah ELSE 0 END) as pengeluaran
            ')
            ->where('created_at', '>=', now()->subDays(30))
            ->where('status', 'valid')
            ->groupByRaw('DATE(created_at)') // ✅ FIXED: gunakan RAW
            ->orderByRaw('DATE(created_at)')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $transaksiHarian
        ]);
    }
}