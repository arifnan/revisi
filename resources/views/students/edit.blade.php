@extends('layouts.app')

@section('title', 'Edit Siswa')

@section('content')
<div class="container mt-4">
    <h2>Edit Siswa</h2>
    <a href="{{ route('students.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    <form action="{{ route('students.update', $student->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input type="text" name="name" class="form-control" value="{{ $student->name }}" required>
        </div>

        <div class="mb-3">
            <label for="gender" class="form-label">Jenis Kelamin</label>
            <select name="gender" class="form-control">
                <option value="1" {{ $student->gender ? 'selected' : '' }}>Laki-Laki</option>
                <option value="0" {{ !$student->gender ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="grade" class="form-label">Kelas</label>
            <input type="text" name="grade" class="form-control" value="{{ $student->grade }}" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Alamat</label>
            <textarea name="address" class="form-control">{{ $student->address }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>
@endsection
