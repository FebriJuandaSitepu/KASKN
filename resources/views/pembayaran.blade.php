{{-- resources/views/topup/index.blade.php --}}

@extends('layouts.main')

@section('title', 'Top Up')

@push('styles')
    <link rel="stylesheet" href="css/pembayaran.css">
@endpush

@section('content')
    <div class="page-header">
        <h1>Transaksi</h1>
        <p>Halaman Pembayaran</p>
    </div>

    <div class="content-area">
        {{-- Tombol Tambah dan Search Bar di atas tabel (sesuai gambar terakhir) --}}
        <div class="table-controls">
            <div class="search-box">
                <label for="search">Search:</label>
                <input type="text" id="search" placeholder="Search...">
                <button type="button"><i class="fas fa-search"></i></button> {{-- Tombol Search terpisah --}}
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Pembayaran</th>
                        <th>ID Pemesanan</th>
                        <th>ID User</th>
                        <th>Metode Pembayaran</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th>Tanggal Bayar</th>
                    </tr>
                </thead>
                <tr>
                    <td colspan="10" style="text-align: center;">Tidak ada data Pembayaran</td>
                </tr>
            </table>
        </div>

        <div class="pagination-section">
            <nav class="pagination">
                <ul class="pagination">
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                    <li class="page-item active"><span class="page-link">1</span></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>
@endsection