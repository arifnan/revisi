@extends('layouts.app')

@section('title', 'Daftar Formulir')

@section('content')
<div class="container-fluid mt-4">

    {{-- Header + Aksi Utama --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold text-dark">Daftar Formulir</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('forms.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle"></i> Tambah
            </a>
            {{-- Hapus atau ubah tombol import global ini jika tidak dibutuhkan lagi --}}
            {{-- <a href="{{ route('responses.import.form') }}" class="btn btn-info shadow-sm">
                <i class="bi bi-upload"></i> Import Excel (Global)
            </a> --}}
        </div>
    </div>

    {{-- Filter Guru --}}
    <form method="GET" action="{{ route('forms.index') }}" class="row g-2 mb-3 align-items-center">
        <div class="col-md-4">
            <select name="teacher_id" class="form-select">
                <option value="">Pilih Guru</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-success">Cari</button>
            <a href="{{ route('forms.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Filter Formulir untuk Respon --}}
    <form method="GET" action="{{ route('responses.index') }}" class="input-group mb-3">
        <select name="form_id" class="form-select">
            <option value="">Pilih Formulir (Filter Respon)</option>
            @foreach(App\Models\Form::all() as $formOption)
                <option value="{{ $formOption->id }}" {{ request('form_id') == $formOption->id ? 'selected' : '' }}>
                    {{ $formOption->title }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Tampilkan Respon</button>
    </form>

    {{-- Ekspor --}}
    <div class="mb-4 d-flex gap-2">
        <a href="{{ route('responses.export.pdf', ['form_id' => request('form_id')]) }}" class="btn btn-danger shadow-sm">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
        <a href="{{ route('responses.export.excel', ['form_id' => request('form_id')]) }}" class="btn btn-success shadow-sm">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Tabel --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 align-middle custom-form-table">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Guru</th>
                            <th>Kode</th>
                            <th>Jumlah Responden</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($forms as $form)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">{{ $form->id }}</td>
                            <td>{{ $form->title }}</td>
                            <td>{{ $form->teacher->name ?? 'N/A' }}</td>
                            <td class="text-center fw-semibold text-primary">{{ $form->form_code }}</td>
                            <td class="text-center">{{ $form->responses_count }}</td>
                            <td class="text-center">
                                <a href="{{ route('responses.detail_by_form', $form->id) }}" class="btn btn-info btn-sm me-1">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('forms.edit', $form->id) }}" class="btn btn-warning btn-sm me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('forms.destroy', $form->id) }}" method="POST" class="d-inline" onsubmit="return confirmDelete(this)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada formulir ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection