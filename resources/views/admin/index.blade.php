@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4 fw-bold text-center">Dashboard</h1>

    {{-- Info Cards --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-white bg-primary shadow-sm rounded">
                <div class="card-body text-center">
                    <h5>Total Pengguna</h5>
                    <h2>{{ $totalPengguna }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-white bg-success shadow-sm rounded">
                <div class="card-body text-center">
                    <h5>Total Pembayaran</h5>
                    <h2>{{ $totalPembayaran }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-white bg-info shadow-sm rounded">
                <div class="card-body text-center">
                    <h5>Total Pemasukan</h5>
                    <h2>Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-white bg-danger shadow-sm rounded">
                <div class="card-body text-center">
                    <h5>Total Pengeluaran</h5>
                    <h2>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifikasi --}}
    <div class="alert alert-warning text-center fw-semibold" role="alert">
        <i class="fa fa-bell me-2"></i> Jangan lupa cek dan validasi transaksi hari ini!
    </div>

    {{-- Tabel Transaksi Terakhir --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-dark text-white fw-semibold">
            <i class="fa fa-clock me-2"></i> Transaksi Terakhir
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Konsumen</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaksiTerakhir as $index => $transaksi)
<tr>
    <td>{{ $index + 1 }}</td>
    <td>{{ $transaksi->konsumen->nama ?? '-' }}</td>
    <td>{{ ucfirst($transaksi->tipe) }}</td>
    <td>Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</td>
    <td>{{ $transaksi->created_at->format('d M Y') }}</td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center">Tidak ada transaksi ditemukan.</td>
</tr>
@endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
