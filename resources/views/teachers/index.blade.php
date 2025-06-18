@extends('layouts.app')

@section('title', 'Daftar Guru')

@section('content')
<div class="container mt-4">
    <h2>Daftar Guru</h2>
    <a href="{{ route('teachers.create') }}" class="btn btn-primary mb-3">Tambah Guru</a>

    <!-- Form Pencarian -->
    <form method="GET" action="{{ route('teachers.index') }}" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <select name="gender" class="form-control">
                    <option value="">Pilih Gender</option>
                    <option value="1" {{ request('gender') == '1' ? 'selected' : '' }}>Laki-Laki</option>
                    <option value="0" {{ request('gender') == '0' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="subject" class="form-control" placeholder="Cari Mata Pelajaran" value="{{ request('subject') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success">Cari</button>
                <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>NIP</th>
                <th>Nama</th>
                <th>Jenis Kelamin</th>
                <th>Email</th>
                <th>Mata Pelajaran</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teachers as $teacher)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $teacher->nip }}</td>
                <td>{{ $teacher->name }}</td>
                <td>{{ $teacher->gender ? 'Laki-Laki' : 'Perempuan' }}</td>
                <td>{{ $teacher->email }}</td>
                <td>{{ $teacher->subject }}</td>
                <td>{{ $teacher->address }}</td>
                <td>
                    <a href="{{ route('teachers.edit', $teacher->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
