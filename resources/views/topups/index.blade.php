@extends('layouts.main')

@section('title', 'Daftar Permintaan Top Up')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Daftar Permintaan Top Up</h2>
    <p class="text-muted mb-4">Halaman ini menampilkan topup dari konsumen, baik manual maupun via Flutter.</p>

    {{-- FORM INPUT MANUAL --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>Input Topup Manual</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.topup.create') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <label for="konsumen_id">Pilih Konsumen:</label>
                        <select name="konsumen_id" class="form-control" required>
                            <option value="">-- Pilih Konsumen --</option>
                            @foreach ($konsumens as $konsumen)
                                <option value="{{ $konsumen->no_identitas }}">{{ $konsumen->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="nominal">Nominal (Rp):</label>
                        <input type="number" name="nominal" class="form-control" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">Simpan Topup Manual</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL TOPUP --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nama Konsumen</th>
                        <th>Nominal</th>
                        <th>Bukti Transfer</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topups as $topup)
                    <tr>
                        <td>{{ $topup->id }}</td>
                        <td>{{ optional($topup->konsumen)->nama ?? '[Konsumen tidak ditemukan]' }}</td>
                        <td>Rp{{ number_format($topup->nominal, 0, ',', '.') }}</td>
                        <td>
                            @if ($topup->bukti_transfer)
                                <a href="{{ asset('storage/' . $topup->bukti_transfer) }}" target="_blank" class="btn btn-link btn-sm">Lihat Bukti</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $topup->status == 'diterima' ? 'bg-success' : ($topup->status == 'ditolak' ? 'bg-danger' : 'bg-warning') }}">
                                {{ ucfirst($topup->status) }}
                            </span>
                            @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

                        </td>
                        <td class="text-center">
    @if ($topup->status === 'pending')
        <form action="{{ route('admin.topup.konfirmasi', $topup->id) }}" method="POST" onsubmit="return confirm('Konfirmasi topup ini?')">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-success">Konfirmasi</button>
        </form>
    @else
        <span class="badge bg-success">✔</span>
    @endif
</td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada permintaan topup.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
