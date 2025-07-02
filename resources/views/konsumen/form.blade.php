@extends('layouts.main')

@section('content')
<div class="container mt-4">
    <h2>{{ $konsumen->exists ? 'Edit Konsumen' : 'Tambah Konsumen' }}</h2>

    {{-- Tampilkan pesan sukses jika ada --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tampilkan error validasi --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Tambah/Edit Konsumen --}}
    <form action="{{ $konsumen->exists ? route('konsumen.update', $konsumen->id) : route('konsumen.store') }}" method="POST">
        @csrf
        @if($konsumen->exists)
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $konsumen->nama) }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Alamat Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $konsumen->email) }}" required>
        </div>

        <div class="mb-3">
            <label for="no_telepon" class="form-label">No. Telepon</label>
            <input type="text" name="no_telepon" class="form-control" value="{{ old('no_telepon', $konsumen->no_telepon) }}">
        </div>

        @if(!$konsumen->exists)
        <div class="mb-3">
            <label for="password" class="form-label">Password Login</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        @endif

        <button type="submit" class="btn btn-primary">{{ $konsumen->exists ? 'Update' : 'Simpan' }}</button>
    </form>
</div>
@endsection
