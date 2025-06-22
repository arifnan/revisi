@extends('layouts.app')

@section('title', 'Tambah Respon untuk Formulir: ' . $form->title)

@section('content')
<div class="container mt-4">
    <h2>Tambah Respon untuk Formulir: {{ $form->title }}</h2>
    <a href="{{ route('responses.detail_by_form', $form->id) }}" class="btn btn-secondary mb-3">Kembali ke Detail Formulir</a>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('responses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="form_id" value="{{ $form->id }}">

                <div class="mb-3">
                    <label for="student_id" class="form-label">Pilih Siswa</label>
                    <select name="student_id" id="student_id" class="form-select" required>
                        <option value="">Pilih Siswa</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="photo" class="form-label">Foto Respon (Opsional)</label>
                    <input type="file" name="photo" id="photo" class="form-control">
                    @error('photo')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="latitude" class="form-label">Latitude (Opsional)</label>
                        <input type="text" name="latitude" id="latitude" class="form-control" value="{{ old('latitude') }}">
                        @error('latitude')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="longitude" class="form-label">Longitude (Opsional)</label>
                        <input type="text" name="longitude" id="longitude" class="form-control" value="{{ old('longitude') }}">
                        @error('longitude')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>
                <h4>Jawaban Pertanyaan</h4>
                @forelse($questions as $question)
                    <div class="mb-4 p-3 border rounded">
                        <p class="mb-2"><strong>{{ $loop->iteration }}. {{ $question->question_text }}</strong>
                            @if ($question->required)
                                <span class="badge bg-danger">Wajib</span>
                            @endif
                        </p>
                        <input type="hidden" name="answers[{{ $loop->index }}][question_id]" value="{{ $question->id }}">

                        @if ($question->question_type == 'Text')
                            <div class="mb-2">
                                <label for="answer_text_{{ $question->id }}" class="form-label">Jawaban Teks</label>
                                <input type="text" name="answers[{{ $loop->index }}][answer_text]" id="answer_text_{{ $question->id }}" class="form-control" {{ $question->required ? 'required' : '' }} value="{{ old('answers.' . $loop->index . '.answer_text') }}">
                                @error('answers.' . $loop->index . '.answer_text')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @elseif ($question->question_type == 'MultipleChoice')
                            <div class="mb-2">
                                <label class="form-label">Pilih Opsi</label>
                                @foreach($question->options as $option)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="answers[{{ $loop->parent->index }}][answer_text]" id="option_{{ $option->id }}" value="{{ $option->option_text }}" {{ old('answers.' . $loop->parent->index . '.answer_text') == $option->option_text ? 'checked' : '' }} {{ $question->required ? 'required' : '' }}>
                                        <input type="hidden" name="answers[{{ $loop->parent->index }}][option_id]" value="{{ $option->id }}">
                                        <label class="form-check-label" for="option_{{ $option->id }}">
                                            {{ $option->option_text }}
                                        </label>
                                    </div>
                                @endforeach
                                @error('answers.' . $loop->index . '.answer_text')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @elseif ($question->question_type == 'Checkbox')
                            <div class="mb-2">
                                <label class="form-label">Pilih Opsi (multiple)</label>
                                {{-- Untuk checkbox, Anda mungkin memerlukan cara yang lebih kompleks untuk menyimpan banyak jawaban, mis., dipisahkan koma atau tabel pivot terpisah.
                                    Untuk kesederhanaan, contoh ini akan menyimpan nilai yang dipisahkan koma di answer_text.
                                --}}
                                @foreach($question->options as $option)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="answers[{{ $loop->parent->index }}][answer_text][]" id="option_{{ $option->id }}" value="{{ $option->option_text }}" {{ in_array($option->option_text, old('answers.' . $loop->parent->index . '.answer_text', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="option_{{ $option->id }}">
                                            {{ $option->option_text }}
                                        </label>
                                    </div>
                                @endforeach
                                @error('answers.' . $loop->index . '.answer_text')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @elseif ($question->question_type == 'LinearScale')
                            <div class="mb-2">
                                <label for="answer_text_{{ $question->id }}" class="form-label">Pilih Skala</label>
                                <select name="answers[{{ $loop->index }}][answer_text]" id="answer_text_{{ $question->id }}" class="form-select" {{ $question->required ? 'required' : '' }}>
                                    <option value="">Pilih Skala</option>
                                    @foreach($question->options as $option)
                                        <option value="{{ $option->option_text }}" {{ old('answers.' . $loop->index . '.answer_text') == $option->option_text ? 'selected' : '' }}>
                                            {{ $option->option_text }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('answers.' . $loop->index . '.answer_text')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @elseif ($question->question_type == 'true_false')
                            <div class="mb-2">
                                <label class="form-label">Pilih</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answers[{{ $loop->index }}][answer_text]" id="true_{{ $question->id }}" value="1" {{ old('answers.' . $loop->index . '.answer_text') === '1' ? 'checked' : '' }} {{ $question->required ? 'required' : '' }}>
                                    <label class="form-check-label" for="true_{{ $question->id }}">True</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answers[{{ $loop->index }}][answer_text]" id="false_{{ $question->id }}" value="0" {{ old('answers.' . $loop->index . '.answer_text') === '0' ? 'checked' : '' }} {{ $question->required ? 'required' : '' }}>
                                    <label class="form-check-label" for="false_{{ $question->id }}">False</label>
                                </div>
                                @error('answers.' . $loop->index . '.answer_text')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @elseif ($question->question_type == 'file_upload')
                            <div class="mb-2">
                                <label for="file_upload_{{ $question->id }}" class="form-label">Upload File</label>
                                <input type="file" name="answers[{{ $loop->index }}][file_upload]" id="file_upload_{{ $question->id }}" class="form-control" {{ $question->required ? 'required' : '' }}>
                                {{-- Input tersembunyi untuk answer_text jika file diunggah --}}
                                <input type="hidden" name="answers[{{ $loop->index }}][answer_text]" value="">
                                @error('answers.' . $loop->index . '.file_upload')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                @empty
                    <p>Tidak ada pertanyaan untuk formulir ini. Silakan tambahkan pertanyaan terlebih dahulu.</p>
                @endforelse

                <button type="submit" class="btn btn-primary">Simpan Respon</button>
            </form>
        </div>
    </div>
</div>
@endsection