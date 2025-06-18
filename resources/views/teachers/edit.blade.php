@extends('layouts.app')

@section('title', 'Edit Guru')

@section('content')
<div class="container mt-4">
    <h2>Edit Guru</h2>
    <a href="{{ route('teachers.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nip" class="form-label">NIP</label>
            <input type="text" name="nip" class="form-control" value="{{ $teacher->nip }}" readonly>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input type="text" name="name" class="form-control" value="{{ $teacher->name }}" required>
        </div>

        <div class="mb-3">
            <label for="gender" class="form-label">Jenis Kelamin</label>
            <select name="gender" class="form-control">
                <option value="1" {{ $teacher->gender ? 'selected' : '' }}>Laki-Laki</option>
                <option value="0" {{ !$teacher->gender ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label">Mata Pelajaran</label>
            <input type="text" name="subject" class="form-control" value="{{ $teacher->subject }}" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Alamat</label>
            <textarea name="address" class="form-control">{{ $teacher->address }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>
@endsection
