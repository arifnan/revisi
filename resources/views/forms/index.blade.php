@extends('layouts.app')

@section('title', 'Daftar Formulir')

@section('content')
<div class="container mt-4">
    <h2>Daftar Formulir</h2>
    <a href="{{ route('forms.create') }}" class="btn btn-primary mb-3">Tambah Formulir</a>

    <!-- Form Pencarian -->
    <form method="GET" action="{{ route('forms.index') }}" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <select name="teacher_id" class="form-control">
                    <option value="">Pilih Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success">Cari</button>
                <a href="{{ route('forms.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
<!-- Filter berdasarkan Form -->
<form method="GET" action="{{ route('responses.index') }}" class="mb-3">
    <div class="input-group">
        <select name="form_id" class="form-select">
            <option value="">Pilih Formulir</option>
            @foreach(App\Models\Form::all() as $form)
                <option value="{{ $form->id }}" {{ request('form_id') == $form->id ? 'selected' : '' }}>
                    {{ $form->title }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </div>
</form>

<!-- Tombol Export -->
<div class="mb-3 d-flex gap-2">
    <a href="{{ route('responses.export.pdf', ['form_id' => request('form_id')]) }}" class="btn btn-danger">Export PDF</a>
    <a href="{{ route('responses.export.excel', ['form_id' => request('form_id')]) }}" class="btn btn-success">Export Excel</a>
</div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Nama Guru</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($forms as $form)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $form->title }}</td>
                <td>{{ $form->description }}</td>
                <td>{{ $form->teacher->name }}</td>
                <td>
                    <a href="{{ route('forms.edit', $form->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('forms.destroy', $form->id) }}" method="POST" class="d-inline">
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
