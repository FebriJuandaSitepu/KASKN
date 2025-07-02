@extends('layouts.main')

@section('title', 'Data Pembayaran')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Data Transaksi Kas</h1>

    {{-- Filter --}}
    <form method="GET" action="{{ route('pembayaran.index') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari Nama Konsumen..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="valid" {{ request('status') == 'valid' ? 'selected' : '' }}>Valid</option>
                <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Terapkan</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Tombol Tambah --}}
    <div class="mb-3">
        <a href="{{ route('pembayaran.create', ['tipe' => 'pemasukan']) }}" class="btn btn-success btn-sm">Tambah Pemasukan</a>
        <a href="{{ route('pembayaran.create', ['tipe' => 'pengeluaran']) }}" class="btn btn-danger btn-sm">Tambah Pengeluaran</a>
    </div>

    {{-- Tabel Data --}}
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nama Konsumen</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Bukti</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembayaran as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->konsumen->nama ?? '-' }}</td>
                    <td>{{ ucfirst($item->tipe) }}</td>
                    <td>Rp{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                    <td>{{ $item->metode }}</td>
                    <td>{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}</td>
                    <td>
                        <form action="{{ route('pembayaran.status', $item->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                <option value="pending" {{ $item->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="valid" {{ $item->status === 'valid' ? 'selected' : '' }}>Valid</option>
                                <option value="ditolak" {{ $item->status === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        @if($item->bukti)
                            <a href="{{ asset('storage/' . $item->bukti) }}" target="_blank" class="btn btn-sm btn-info">Lihat</a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        {{-- Status Action (Jika Pending) --}}
                        @if($item->status === 'pending')
                            <form action="{{ route('pembayaran.status', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="valid">
                                <button class="btn btn-success btn-sm">Valid</button>
                            </form>
                            <form action="{{ route('pembayaran.status', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="ditolak">
                                <button class="btn btn-danger btn-sm">Tolak</button>
                            </form>
                        @endif

                        {{-- Edit --}}
                        <a href="{{ route('pembayaran.edit', $item->id) }}" class="btn btn-warning btn-sm ms-1">Edit</a>

                        {{-- Hapus --}}
                        <form action="{{ route('pembayaran.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data transaksi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="d-flex justify-content-end">
        {{ $pembayaran->withQueryString()->links() }}
    </div>
</div>
@endsection
