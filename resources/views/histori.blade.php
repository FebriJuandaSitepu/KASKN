@extends('layouts.main')
@section('title', 'Histori Top-up')
@section('content')
<h2>Histori Top-up</h2>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nama Pengguna</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($topups as $topup)
        <tr>
            <td>{{ $topup->nama_user }}</td>
            <td>Rp {{ number_format($topup->jumlah, 0, ',', '.') }}</td>
            <td>{{ ucfirst($topup->status) }}</td>
            <td>{{ \Carbon\Carbon::parse($topup->created_at)->format('d M Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
