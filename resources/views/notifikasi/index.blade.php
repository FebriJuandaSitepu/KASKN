@extends('layouts.main')

@section('title', 'Notifikasi')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="text-dark">📢 Notifikasi</h2>
        <a href="{{ route('notifikasi.kirim.form') }}" class="btn btn-warning">
            <i class="fa fa-paper-plane me-1"></i> Kirim Notifikasi
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($notifikasi->count())
        <div class="list-group shadow-sm rounded">
            @foreach($notifikasi as $notif)
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 text-primary"><i class="fa fa-bell me-1"></i> {{ $notif->judul }}</h5>
                        <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-1 text-secondary">{{ $notif->pesan }}</p>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">
            <i class="fa fa-info-circle me-1"></i> Tidak ada notifikasi.
        </div>
    @endif
</div>
@endsection
