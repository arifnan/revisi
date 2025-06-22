@extends('layouts.app')

@section('title', 'Import Jawaban Responden')

@section('content')
<div class="container mt-4">
    <h2>Import Jawaban Responden untuk Formulir: {{ $form->title }}</h2>
    <a href="{{ route('responses.detail_by_form', $form->id) }}" class="btn btn-secondary mb-3">Kembali ke Detail Formulir</a>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {!! session('error') !!}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <p>Silakan unduh template Excel berikut untuk contoh format yang benar:</p>
            {{-- Link unduh template Excel, sesuaikan dengan lokasi file template Anda --}}
            <a href="{{ asset('path/to/your/responses_template.xlsx') }}" class="btn btn-info mb-3" download>Unduh Template Excel</a> 

            <form action="{{ route('responses.import.excel.by_form', $form->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="file" class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" name="file" id="file" class="form-control" required>
                    @error('file')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Import</button>
            </form>
            <hr>
            <p><strong>Format Excel/CSV yang Diharapkan:</strong></p>
            <p>Pastikan file Anda memiliki header berikut pada baris pertama. Setiap baris mewakili **satu jawaban untuk satu pertanyaan dari satu responden**. Gunakan kombinasi `student_email`, `form_code`, dan `submitted_at` untuk mengelompokkan jawaban ke dalam satu respon.</p>
            <ul>
                <li>`form_code` (Kode unik Formulir, contoh: FORM123) - Wajib, harus ada di tabel `forms`</li>
                <li>`student_email` (Email Siswa, contoh: siswa@mail.com) - Wajib, harus ada di tabel `students`</li>
                <li>`submitted_at` (Waktu Respon Disubmit, format: `YYYY-MM-DD HH:MM:SS`, contoh: 2025-01-15 10:00:00) - Wajib</li>
                <li>`question_text` (Teks Pertanyaan, contoh: Apa warna favorit Anda?) - Wajib, harus ada di formulir terkait (yaitu form yang sedang di-import)</li>
                <li>`answer_text` (Teks Jawaban, contoh: Biru) - Opsional, tergantung tipe pertanyaan</li>
                <li>`option_text` (Teks Opsi yang dipilih, relevan untuk Pilihan Ganda/Checkbox, opsional, contoh: Opsi A)</li>
                <li>`photo_url` (URL Gambar/Foto Respon, opsional, contoh: `http://example.com/photo1.jpg`)</li>
                <li>`latitude` (Latitude Lokasi Respon, opsional, numerik)</li>
                <li>`longitude` (Longitude Lokasi Respon, opsional, numerik)</li>
                <li>`formatted_address` (Alamat format manusia, opsional)</li>
                <li>`file_url` (URL file jika pertanyaan bertipe file_upload, opsional, contoh: `http://example.com/doc.pdf`)</li>
            </ul>
            <p><strong>Penting:</strong> Untuk satu respons lengkap dari seorang siswa, semua baris jawaban harus memiliki `form_code`, `student_email`, dan `submitted_at` yang sama.</p>
        </div>
    </div>
</div>
@endsection