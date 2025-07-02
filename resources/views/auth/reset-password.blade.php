@extends('layouts.main')

@section('content')
<div class="container mt-5">
    <h4>Reset Password</h4>
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label>Email:</label>
            <input type="email" name="email" value="{{ old('email', $email ?? '') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password Baru:</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Konfirmasi Password:</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
</div>
@endsection
