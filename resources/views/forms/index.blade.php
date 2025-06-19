@extends('layouts.app')

@section('title', 'Daftar Formulir')

@section('content')
<div class="container mt-4">
    <h2>Daftar Formulir</h2>
    <div class="mb-3">
        <a href="{{ route('forms.create') }}" class="btn btn-primary">Tambah Formulir</a>
        <a href="{{ route('responses.import.form') }}" class="btn btn-info">Import Respon Excel</a> {{-- Tambahkan ini --}}
    </div>

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

    <form method="GET" action="{{ route('responses.index') }}" class="mb-3">
        <div class="input-group">
            <select name="form_id" class="form-select">
                <option value="">Pilih Formulir (Filter Respon)</option>
                @foreach(App\Models\Form::all() as $formOption)
                    <option value="{{ $formOption->id }}" {{ request('form_id') == $formOption->id ? 'selected' : '' }}>
                        {{ $formOption->title }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Tampilkan Respon</button>
        </div>
    </form>

    <div class="mb-3 d-flex gap-2">
        <a href="{{ route('responses.export.pdf', ['form_id' => request('form_id')]) }}" class="btn btn-danger">Export PDF</a>
        <a href="{{ route('responses.export.excel', ['form_id' => request('form_id')]) }}" class="btn btn-success">Export Excel</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered custom-form-table"> {{-- Tambahkan kelas kustom di sini --}}
            <thead>
                <tr>
                    <th>#</th>
                    <th>ID Formulir</th>
                    <th>Judul</th>
                    {{-- Hapus kolom Deskripsi --}}
                    <th>Guru</th>
                    <th>Kode Formulir</th>
                    <th>Jumlah Responden</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($forms as $form)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $form->id }}</td>
                    <td>{{ $form->title }}</td>
                    {{-- Hapus data kolom Deskripsi --}}
                    <td>{{ $form->teacher->name ?? 'N/A' }}</td>
                    <td>{{ $form->form_code }}</td>
                    <td>{{ $form->responses_count }}</td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('responses.detail_by_form', $form->id) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                        <a href="{{ route('forms.edit', $form->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('forms.destroy', $form->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus formulir ini? Semua pertanyaan dan jawaban terkait akan ikut terhapus.')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada formulir yang ditemukan.</td> {{-- Sesuaikan colspan --}}
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection