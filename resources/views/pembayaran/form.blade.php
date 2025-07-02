@extends('layouts.main')

@section('content')
<div class="container">
    <h2>Input Pembayaran Konsumen</h2>

    <form action="{{ route('pembayaran.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="konsumen_id" class="form-label">Pilih Konsumen</label>
            <select name="konsumen_id" class="form-control" required>
                @foreach($konsumens as $konsumen)
                    <option value="{{ $konsumen->no_identitas }}">{{ $konsumen->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="tipe" class="form-label">Tipe Pembayaran</label>
            <select name="tipe" class="form-control" required>
                <option value="pemasukan">Pemasukan</option>
                <option value="pengeluaran">Pengeluaran</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="jumlah" class="form-label">Jumlah (Rp)</label>
            <input type="number" name="jumlah" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="metode" class="form-label">Metode Pembayaran</label>
            <input type="text" name="metode" class="form-control">
        </div>

        <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}">
        </div>

        <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
    </form>
</div>
@endsection
