@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-dark">Daftar Formulir</h1>
        <a href="{{ route('forms.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Buat Formulir
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white fw-semibold">
            <i class="bi bi-file-earmark-text me-2"></i> Formulir Tersedia
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Kode Formulir</th>
                            <th>Guru</th>
                            <th>Tanggal Dibuat</th>
                            <th>Responses</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($forms as $index => $form)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $form->title }}</td>
                            <td>{{ $form->form_code }}</td>
                            <td>{{ $form->teacher->name }}</td>
                            <td>{{ $form->created_at->format('d M Y') }}</td>
                            <td>{{ $form->responses_count }}</td>
                            <td>
                                <a href="{{ route('forms.show', $form->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('forms.edit', $form->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('forms.destroy', $form->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $forms->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
