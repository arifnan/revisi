@extends('layouts.app')

@section('title', 'Tambah Pertanyaan')

@section('content')
<div class="container mt-4">
    <h2>Tambah Pertanyaan untuk Formulir: {{ $form->title }}</h2>
    <a href="{{ route('responses.detail_by_form', $form->id) }}" class="btn btn-secondary mb-3">Kembali ke Detail Formulir</a>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('questions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="form_id" value="{{ $form->id }}">

                <div class="mb-3">
                    <label for="question_text" class="form-label">Teks Pertanyaan</label>
                    <textarea name="question_text" id="question_text" class="form-control" rows="3" required>{{ old('question_text') }}</textarea>
                    @error('question_text')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="question_type" class="form-label">Tipe Pertanyaan</label>
                    <select name="question_type" id="question_type" class="form-select" required>
                        <option value="">Pilih Tipe</option>
                        <option value="Text" {{ old('question_type') == 'Text' ? 'selected' : '' }}>Teks Singkat</option>
                        <option value="MultipleChoice" {{ old('question_type') == 'MultipleChoice' ? 'selected' : '' }}>Pilihan Ganda</option>
                        <option value="Checkbox" {{ old('question_type') == 'Checkbox' ? 'selected' : '' }}>Check Box</option>
                        <option value="LinearScale" {{ old('question_type') == 'LinearScale' ? 'selected' : '' }}>Skala Linear</option>
                        <option value="true_false" {{ old('question_type') == 'true_false' ? 'selected' : '' }}>True/False</option>
                        <option value="file_upload" {{ old('question_type') == 'file_upload' ? 'selected' : '' }}>Upload File</option>
                    </select>
                    @error('question_type')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="required" id="required" class="form-check-input" value="1" {{ old('required') ? 'checked' : '' }}>
                    <label class="form-check-label" for="required">Wajib diisi</label>
                    @error('required')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div id="options-container" style="display: none;">
                    <label class="form-label">Opsi (satu opsi per baris)</label>
                    <textarea name="options_text" id="options_text" class="form-control" rows="5" placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3">{{ old('options_text') }}</textarea>
                    @error('options')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan Pertanyaan</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const questionTypeSelect = document.getElementById('question_type');
        const optionsContainer = document.getElementById('options-container');
        const optionsTextarea = document.getElementById('options_text');

        function toggleOptionsVisibility() {
            const selectedType = questionTypeSelect.value;
            if (['MultipleChoice', 'Checkbox', 'LinearScale'].includes(selectedType)) {
                optionsContainer.style.display = 'block';
                optionsTextarea.required = true; // Opsional: membuat opsi wajib jika tipe tertentu
            } else {
                optionsContainer.style.display = 'none';
                optionsTextarea.required = false;
                optionsTextarea.value = ''; // Kosongkan nilai jika tidak relevan
            }
        }

        // Jalankan saat halaman dimuat untuk old() value
        toggleOptionsVisibility();

        // Jalankan saat tipe pertanyaan berubah
        questionTypeSelect.addEventListener('change', toggleOptionsVisibility);

        // Sebelum submit, pecah teks opsi menjadi array untuk dikirim ke backend
        document.querySelector('form').addEventListener('submit', function(event) {
            if (['MultipleChoice', 'Checkbox', 'LinearScale'].includes(questionTypeSelect.value)) {
                const optionsValue = optionsTextarea.value.trim();
                if (optionsValue) {
                    const optionsArray = optionsValue.split('\n').map(line => line.trim()).filter(line => line !== '');
                    // Buat input tersembunyi untuk setiap opsi
                    optionsArray.forEach((option, index) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `options[${index}]`;
                        input.value = option;
                        this.appendChild(input);
                    });
                }
                // Hapus textarea agar tidak dikirim ganda
                optionsTextarea.name = '';
            }
        });
    });
</script>
@endsection