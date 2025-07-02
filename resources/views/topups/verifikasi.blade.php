{{-- resources/views/topup/verifikasi.blade.php --}}
@extends('layouts.main')
@section('title', 'Verifikasi Topup Manual')

@section('content')
    <div class="page-header">
        <h1>Form Top Up Manual Konsumen</h1>
        <p>Masukkan nominal topup untuk konsumen secara manual tanpa bukti transfer.</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($konsumen)
        <form method="POST" action="{{ route('admin.topup.manual') }}">
            @csrf
            <input type="hidden" name="konsumen_id" value="{{ $konsumen->id }}">

            <div class="form-group mb-3">
                <label>Nama Konsumen:</label>
                <input type="text" class="form-control" value="{{ $konsumen->nama }}" disabled>
            </div>

            <div class="form-group mb-3">
                <label>Nominal Top Up:</label>
                <input type="number" name="nominal" class="form-control" required min="1000">
            </div>

            <button type="submit" class="btn btn-primary">Topup Sekarang</button>
        </form>
    @else
        <p class="text-danger">Data konsumen tidak tersedia.</p>
    @endif
@endsection