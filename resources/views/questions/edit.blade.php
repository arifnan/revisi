@extends('layouts.app')

@section('title', 'Edit Pertanyaan')

@section('content')
<div class="container mt-4">
    <h2>Edit Pertanyaan</h2>
    <form action="{{ route('questions.update', $question->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Pertanyaan</label>
            <input type="text" name="question_text" class="form-control" value="{{ $question->question_text }}" required>
        </div>
        <div class="mb-3">
            <label>Tipe Jawaban</label>
            <select name="question_type" class="form-select">
                <option value="short_text" {{ $question->question_type == 'short_text' ? 'selected' : '' }}>Jawaban Singkat</option>
                <option value="long_text" {{ $question->question_type == 'long_text' ? 'selected' : '' }}>Jawaban Panjang</option>
                <option value="multiple_choice" {{ $question->question_type == 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda</option>
                <option value="checkbox" {{ $question->question_type == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                <option value="dropdown" {{ $question->question_type == 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                <option value="true_false" {{ $question->question_type == 'true_false' ? 'selected' : '' }}>True / False</option>
                <option value="file_upload" {{ $question->question_type == 'file_upload' ? 'selected' : '' }}>Lampiran</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Formulir</label>
            <select name="form_id" class="form-select">
                @foreach(App\Models\Form::all() as $form)
                    <option value="{{ $form->id }}" {{ $question->form_id == $form->id ? 'selected' : '' }}>{{ $form->title }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('questions.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
