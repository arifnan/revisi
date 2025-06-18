@extends('layouts.app')

@section('title', 'Import Siswa dari Excel')

@section('content')
<div class="container mt-4">
    <h2>Import Siswa dari Excel</h2>
    <a href="{{ route('students.index') }}" class="btn btn-secondary mb-3">Kembali ke Daftar Siswa</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{!! session('error') !!}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Unggah File Excel</h5>
            <form action="{{ route('students.import.excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="file" class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" name="file" id="file" class="form-control" required>
                    @error('file')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Import Data</button>
            </form>
            <hr>
            <p><strong>Format Excel yang Diharapkan:</strong></p>
            <p>Pastikan file Excel Anda memiliki header berikut pada baris pertama:</p>
            <ul>
                <li><code>name</code> (Nama Siswa)</li>
                <li><code>gender</code> (Jenis Kelamin: 1 untuk Laki-laki, 0 untuk Perempuan)</li>
                <li><code>email</code> (Email, harus unik)</li>
                <li><code>password</code> (Password, minimal 6 karakter)</li>
                <li><code>grade</code> (Kelas)</li>
                <li><code>address</code> (Alamat, opsional)</li>
            </ul>
            <p>Contoh: <br>
            `name`, `gender`, `email`, `password`, `grade`, `address`<br>
            `Budi Santoso`, `1`, `budi.santoso@example.com`, `password123`, `10A`, `Jl. Merdeka No. 1`<br>
            `Siti Aminah`, `0`, `siti.aminah@example.com`, `password456`, `11B`, `Perumahan Indah Blok C`
            </p>
        </div>
    </div>
</div>
@endsection