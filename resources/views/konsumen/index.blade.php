@extends('layouts.main')

@section('title', 'Data Konsumen')

@section('content')
<div class="d-flex justify-content-center mt-4">
    <div class="w-100" style="max-width: 1100px;">
        <h1 class="mb-4 text-center">Data Konsumen</h1>

        {{-- Tampilkan pesan sukses --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Tombol Tambah Konsumen --}}
        <div class="mb-3 text-end">
            <a href="{{ route('konsumen.create') }}" class="btn btn-primary">+ Tambah Konsumen</a>
        </div>

        {{-- Cek apakah ada data --}}
        @if($konsumens->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No. Identitas</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No Telepon</th>
                            <th style="width:220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($konsumens as $konsumen)
                        <tr>
                            <td>{{ $konsumen->no_identitas }}</td>
                            <td>{{ $konsumen->nama }}</td>
                            <td>{{ $konsumen->email }}</td>
                            <td>{{ $konsumen->no_telepon ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap justify-content-center">
                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('konsumen.edit', $konsumen->no_identitas) }}" class="btn btn-warning btn-sm">Edit</a>

                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('konsumen.destroy', $konsumen->no_identitas) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus konsumen ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>

                                    {{-- Tombol Reset Password --}}
                                    <form action="{{ route('konsumen.resetPassword', $konsumen->no_identitas) }}" method="POST" onsubmit="return confirm('Reset password konsumen ini ke default?')">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm">Reset Password</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info text-center">
                Belum ada data konsumen untuk ditampilkan.
            </div>
        @endif
    </div>
</div>
@endsection
