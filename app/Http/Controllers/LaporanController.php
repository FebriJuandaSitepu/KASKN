<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Konsumen;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Laporan konsumen dengan transaksi per bulan
     */
    public function laporanKonsumen()
    {
        // Ambil semua konsumen
        $konsumens = Konsumen::all();

        // Siapkan array rekap dengan GROUP BY yang benar
        $rekap = [];

        // Untuk setiap konsumen, ambil total pembayaran per bulan
        foreach ($konsumens as $konsumen) {
            $rekap[$konsumen->no_identitas] = Pembayaran::selectRaw('
                    MONTH(created_at) as bulan, 
                    SUM(jumlah) as total
                ')
                ->where('konsumen_id', $konsumen->no_identitas)
                ->where('status', 'valid') // Hanya yang valid
                ->whereYear('created_at', date('Y')) // Tahun sekarang
                ->groupByRaw('MONTH(created_at)') // ✅ FIXED: gunakan groupByRaw
                ->get();
        }

        // Kirim ke view
        return view('laporan.index', compact('konsumens', 'rekap'));
    }

    /**
     * Laporan per periode
     */
    public function laporanPeriode(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'konsumen_id' => 'nullable|exists:konsumens,no_identitas',
            'tipe' => 'nullable|in:pemasukan,pengeluaran'
        ]);

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

        $pembayaran = $query->orderBy('tanggal', 'desc')->paginate(20);

        // Ringkasan periode
        $ringkasan = [
            'total_transaksi' => $pembayaran->total(),
            'total_pemasukan' => $query->where('tipe', 'pemasukan')->sum('jumlah'),
            'total_pengeluaran' => $query->where('tipe', 'pengeluaran')->sum('jumlah'),
        ];
        $ringkasan['saldo_bersih'] = $ringkasan['total_pemasukan'] - $ringkasan['total_pengeluaran'];

        $konsumens = Konsumen::all(); // Untuk dropdown filter

        return view('laporan.periode', compact('pembayaran', 'ringkasan', 'konsumens'));
    }

    /**
     * Export laporan (placeholder)
     */
    public function export(Request $request)
    {
        // TODO: Implementasi export dengan Laravel Excel
        return back()->with('info', 'Fitur export sedang dalam pengembangan');
    }

    /**
     * Laporan harian untuk dashboard
     */
    public function laporanHarian()
    {
        $transaksiHarian = Pembayaran::selectRaw('
                DATE(created_at) as tanggal,
                COUNT(*) as jumlah_transaksi,
                SUM(CASE WHEN tipe = "pemasukan" THEN jumlah ELSE 0 END) as total_pemasukan,
                SUM(CASE WHEN tipe = "pengeluaran" THEN jumlah ELSE 0 END) as total_pengeluaran
            ')
            ->where('created_at', '>=', now()->subDays(30))
            ->where('status', 'valid')
            ->groupByRaw('DATE(created_at)') // ✅ FIXED: gunakan groupByRaw
            ->orderByRaw('DATE(created_at) DESC')
            ->get();

        return view('laporan.harian', compact('transaksiHarian'));
    }

    /**
     * Laporan bulanan
     */
    public function laporanBulanan()
    {
        $transaksiBulanan = Pembayaran::selectRaw('
                YEAR(created_at) as tahun,
                MONTH(created_at) as bulan,
                COUNT(*) as jumlah_transaksi,
                SUM(CASE WHEN tipe = "pemasukan" THEN jumlah ELSE 0 END) as total_pemasukan,
                SUM(CASE WHEN tipe = "pengeluaran" THEN jumlah ELSE 0 END) as total_pengeluaran
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->where('status', 'valid')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)') // ✅ FIXED: sudah benar
            ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
            ->get();

        return view('laporan.bulanan', compact('transaksiBulanan'));
    }

    /**
     * API endpoint untuk chart data
     */
    public function chartData(Request $request)
    {
        $periode = $request->get('periode', '7'); // default 7 hari

        $data = Pembayaran::selectRaw('
                DATE(created_at) as tanggal,
                SUM(CASE WHEN tipe = "pemasukan" THEN jumlah ELSE 0 END) as pemasukan,
                SUM(CASE WHEN tipe = "pengeluaran" THEN jumlah ELSE 0 END) as pengeluaran
            ')
            ->where('created_at', '>=', now()->subDays((int)$periode))
            ->where('status', 'valid')
            ->groupByRaw('DATE(created_at)') // ✅ FIXED: gunakan groupByRaw
            ->orderByRaw('DATE(created_at)')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}