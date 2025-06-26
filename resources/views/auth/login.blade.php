@extends('layouts.auth-layout')

@section('title', 'Login')

@section('content')
<h2 class="text-center mb-4">Login</h2>

<!-- Menampilkan pesan error jika email/password salah -->
@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('login') }}" method="POST">
    @csrf
    <div class="mb-3">
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               placeholder="Email address" value="{{ old('email') }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="mb-3">
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
               placeholder="Password" required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100">LOGIN</button>
</form>

@endsection
