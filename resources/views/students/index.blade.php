@extends('layouts.app')

@section('title', 'Daftar Siswa')

@section('content')
<div class="container mt-4">
    <h2>Daftar Siswa</h2>
    <a href="{{ route('students.create') }}" class="btn btn-primary mb-3">Tambah Siswa</a>

    <!-- Form Pencarian -->
    <form method="GET" action="{{ route('students.index') }}" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <select name="gender" class="form-control">
                    <option value="">Pilih Gender</option>
                    <option value="1" {{ request('gender') == '1' ? 'selected' : '' }}>Laki-Laki</option>
                    <option value="0" {{ request('gender') == '0' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="grade" class="form-control">
                    <option value="">Pilih Kelas</option>
                    <option value="10" {{ request('grade') == '10' ? 'selected' : '' }}>10</option>
                    <option value="11" {{ request('grade') == '11' ? 'selected' : '' }}>11</option>
                    <option value="12" {{ request('grade') == '12' ? 'selected' : '' }}>12</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success">Cari</button>
                <a href="{{ route('students.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Jenis Kelamin</th>
                <th>Email</th>
                <th>Kelas</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->gender ? 'Laki-Laki' : 'Perempuan' }}</td>
                <td>{{ $student->email }}</td>
                <td>{{ $student->grade }}</td>
                <td>{{ $student->address }}</td>
                <td>
                    <a href="{{ route('students.edit', $student->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline">
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
