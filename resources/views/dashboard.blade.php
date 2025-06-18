@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container mt-4">
    <h2>Dashboard Admin</h2>
    <p>Selamat datang di sistem E-Form! Kelola formulir, pertanyaan, dan jawaban user.</p>

    <div class="row">
        <!-- Kelola Formulir -->
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Kelola Formulir</h5>
                    <p class="card-text">Buat, edit, dan hapus formulir.</p>
                    <a href="{{ route('forms.index') }}" class="btn btn-light w-100">Akses</a>
                </div>
            </div>
        </div>

        <!-- Kelola Pertanyaan -->
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Kelola Pertanyaan</h5>
                    <p class="card-text">Tambah, edit, dan hapus pertanyaan.</p>
                    <a href="{{ route('questions.index') }}" class="btn btn-light w-100">Akses</a>
                </div>
            </div>
        </div>

        <!-- Lihat Jawaban -->
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Lihat Jawaban</h5>
                    <p class="card-text">Lihat jawaban user dalam formulir.</p>
                    <a href="{{ route('responses.index') }}" class="btn btn-light w-100">Akses</a>
                </div>
            </div>
        </div>

        <!-- Kelola Admin -->
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-body">
                    <h5 class="card-title">Kelola Admin</h5>
                    <p class="card-text">Tambah, edit, dan hapus admin.</p>
                    <a href="{{ route('admin.index') }}" class="btn btn-light w-100">Akses</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
