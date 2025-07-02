@extends('layouts.main')

@section('title', 'Detail Top Up')

@section('content')
<div class="container mt-4">
    <h2>Detail Permintaan Top Up</h2>

    <div class="card mt-3">
        <div class="card-body">
            <p><strong>Nama Konsumen:</strong> {{ optional($topup->konsumen)->nama ?? '[Konsumen tidak ditemukan]' }}</p>
            <p><strong>Nominal:</strong> Rp{{ number_format($topup->nominal, 0, ',', '.') }}</p>
            <p><strong>Status:</strong> 
                <span class="badge {{ $topup->status == 'diterima' ? 'bg-success' : ($topup->status == 'ditolak' ? 'bg-danger' : 'bg-warning') }}">
                    {{ ucfirst($topup->status) }}
                </span>
            </p>
            <p><strong>Bukti Transfer:</strong> 
                @if ($topup->bukti_transfer)
                    <a href="{{ asset('storage/' . $topup->bukti_transfer) }}" target="_blank">Lihat Bukti</a>
                @else
                    <span class="text-muted">-</span>
                @endif
            </p>
        </div>
    </div>
</div>
@endsection
