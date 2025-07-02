@extends('layouts.main')

@section('title', 'Edit Pembayaran')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Edit Pembayaran</h1>

    <form action="{{ route('pembayaran.update', $pembayaran->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Tipe --}}
        <div class="mb-3">
            <label for="tipe" class="form-label">Tipe</label>
            <select name="tipe" id="tipe" class="form-select" required>
                <option value="pemasukan" {{ $pembayaran->tipe === 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                <option value="pengeluaran" {{ $pembayaran->tipe === 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
            </select>
        </div>

        {{-- Jumlah --}}
        <div class="mb-3">
            <label for="jumlah" class="form-label">Jumlah</label>
            <input type="number" name="jumlah" id="jumlah" class="form-control" value="{{ old('jumlah', $pembayaran->jumlah) }}" required>
        </div>

        {{-- Metode --}}
        <div class="mb-3">
            <label for="metode" class="form-label">Metode</label>
            <input type="text" name="metode" id="metode" class="form-control" value="{{ old('metode', $pembayaran->metode) }}" required>
        </div>

        {{-- Tanggal --}}
        <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal', $pembayaran->tanggal) }}" required>
        </div>

        {{-- Bukti --}}
        <div class="mb-3">
            <label for="bukti" class="form-label">Bukti Pembayaran (opsional)</label>
            <input type="file" name="bukti" id="bukti" class="form-control">

            @if($pembayaran->bukti)
                <p class="mt-2">Bukti lama: 
                    <a href="{{ asset('storage/' . $pembayaran->bukti) }}" target="_blank" class="btn btn-sm btn-info">Lihat</a>
                </p>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
