@extends('layouts.main')

@section('content')
<div class="container mt-4">
    <h2>Form Tambah Pembayaran</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Konsumen --}}
        <div class="mb-3">
            <label for="konsumen_id">Pilih Konsumen</label>
            <select name="konsumen_id" class="form-control" required>
                <option value="">-- Pilih Konsumen --</option>
                @foreach($konsumens as $konsumen)
                    <option value="{{ $konsumen->no_identitas }}" {{ old('konsumen_id') == $konsumen->no_identitas ? 'selected' : '' }}>
                        {{ $konsumen->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tipe --}}
        <div class="mb-3">
            <label for="tipe">Tipe Pembayaran</label>
            <select name="tipe" class="form-control" required>
                <option value="">-- Pilih Tipe --</option>
                <option value="pemasukan" {{ old('tipe', $tipe ?? '') == 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                <option value="pengeluaran" {{ old('tipe', $tipe ?? '') == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
            </select>
        </div>

        {{-- Jumlah --}}
        <div class="mb-3">
            <label for="jumlah">Jumlah</label>
            <input type="number" name="jumlah" class="form-control" value="{{ old('jumlah') }}" required>
        </div>

        {{-- Metode --}}
        <div class="mb-3">
            <label for="metode">Metode Pembayaran</label>
            <input type="text" name="metode" class="form-control" value="{{ old('metode') }}" required>
        </div>

        {{-- Tanggal --}}
        <div class="mb-3">
            <label for="tanggal">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}" required>
        </div>

        {{-- Bukti --}}
        <div class="mb-3">
            <label for="bukti">Bukti Pembayaran (opsional)</label>
            <input type="file" name="bukti" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
