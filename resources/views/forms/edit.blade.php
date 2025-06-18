@extends('layouts.app')

@section('title', 'Edit Formulir')

@section('content')
<div class="container mt-4">
    <h2>Edit Formulir</h2>
    <form action="{{ route('forms.update', $form->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="title" class="form-label">Judul Formulir</label>
            <input type="text" name="title" class="form-control" value="{{ $form->title }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control">{{ $form->description }}</textarea>
        </div>
        <div class="mb-3">
            <label for="teacher_id" class="form-label">Pilih Guru</label>
            <select name="teacher_id" class="form-select" required>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ $form->teacher_id == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('forms.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
