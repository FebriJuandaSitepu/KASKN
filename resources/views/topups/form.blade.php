@extends('layouts.main')

@section('content')
<div class="container">
    <h2>Topup Saldo Konsumen</h2>

    <form action="{{ route('topups.store') }}" method="POST">
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
            <label for="nominal" class="form-label">Jumlah Topup (Rp)</label>
            <input type="number" name="nominal" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Topup</button>
    </form>
</div>
@endsection
