@extends('layouts.main')

@section('title', 'Informasi Aplikasi')

@push('styles')
    <link rel="stylesheet" href="/css/informasi.css">
@endpush

@section('content')
<div class="dashboard-content">
    <div class="content-header mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">📘 Informasi Aplikasi</h1>
        <p class="text-sm text-gray-500">Tentang sistem manajemen kas digital KasKN</p> 
    </div>

    <div class="bg-white shadow-xl rounded-2xl p-6 border border-gray-200 max-w-3xl mx-auto">
        <div class="text-gray-700 text-base leading-relaxed tracking-wide">
            <p class="mb-4">Selamat datang di <strong>KasKN</strong> – platform manajemen kas digital yang membantu Anda 
            mencatat dan memantau seluruh aktivitas keuangan secara real-time.</p>

            <p class="mb-4">Aplikasi ini dilengkapi dengan fitur <strong>pencatatan pemasukan & pengeluaran</strong>, 
            <strong>top-up saldo otomatis/manual</strong>, notifikasi real-time, dan laporan lengkap per pengguna.</p>

            <p class="mb-4">Kami berkomitmen untuk memberikan pengalaman pengelolaan kas yang
            <span class="font-semibold text-indigo-600">mudah, aman, efisien</span>, dan 
            <span class="font-semibold text-indigo-600">user-friendly</span> untuk kebutuhan organisasi Anda.</p>
        </div>
    </div>
</div>
@endsection
