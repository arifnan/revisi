@extends('layouts.app')

@section('title', 'Tambah Pertanyaan')

@section('content')
<div class="container mt-4">
    <h2>Tambah Pertanyaan</h2>
    <form action="{{ route('questions.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Pertanyaan</label>
            <input type="text" name="question_text" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tipe Jawaban</label>
            <select name="question_type" class="form-select">
                <option value="short_text">Jawaban Singkat</option>
                <option value="long_text">Jawaban Panjang</option>
                <option value="multiple_choice">Pilihan Ganda</option>
                <option value="checkbox">Checkbox</option>
                <option value="dropdown">Dropdown</option>
                <option value="true_false">True / False</option>
                <option value="file_upload">Lampiran</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Formulir</label>
            <select name="form_id" class="form-select">
                @foreach(App\Models\Form::all() as $form)
                    <option value="{{ $form->id }}">{{ $form->title }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('questions.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
