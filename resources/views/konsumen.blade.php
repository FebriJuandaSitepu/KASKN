@extends('layouts.main') {{-- Menggunakan layout utama --}}

@section('title', 'Data Konsumen') {{-- Judul untuk tab browser --}}

@push('styles') {{-- Mendorong CSS spesifik halaman ke stack 'styles' --}}
    <link rel="stylesheet" href="/css/konsumen.css">
@endpush

@section('content') {{-- Konten untuk bagian 'content' di layout --}}
    <div class="dashboard-content">
        <div class="content-header">
            <h1>Konsumen</h1>
            <p>Data Konsumen</p> {{-- Sesuai gambar --}}
        </div>

        {{-- Bagian Pencarian --}}
        <div class="search-section">
            <form action="{{ route('konsumen') }}" method="GET">
                <label for="search">Search :</label>
                <input type="text" id="search" name="search" placeholder="Search..." value="{{ request('search') }}">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        {{-- Bagian Tabel Data Konsumen --}}
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No Identitas</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Saldo</th>
                        <th>Password</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($konsumens as $konsumen)
                        <tr>
                            <td>{{ $konsumen->no_identitas }}</td>
                            <td>{{ $konsumen->nama }}</td>
                            <td>{{ $konsumen->email }}</td>
                            <td>{{ $konsumen->no_telepon ?? '-' }}</td> {{-- Tampilkan '-' jika null --}}
                            <td>
                                Rp {{ number_format($konsumen->saldo, 0, ',', '.') }}
                            </td>
                            <td>{{ Str::limit($konsumen->password, 8, 'xxx') }}</td> {{-- Tampilkan sebagian hash password --}}
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center;">Tidak ada data konsumen.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bagian Paginasi --}}
        <div class="pagination-section">
            {{ $konsumens->links('vendor.pagination.custom-pagination') }} {{-- Menggunakan custom pagination view --}}
        </div>

    </div>
@endsection