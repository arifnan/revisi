@extends('layouts.app')

@section('title', 'Detail Respon Formulir: ' . $form->title)

@section('content')
<div class="container mt-4">
    <h2>Detail Respon Formulir: {{ $form->title }}</h2>
    <p>Deskripsi: {{ $form->description }}</p>
    <p>Kode Formulir: {{ $form->form_code }}</p>
    <a href="{{ route('forms.index') }}" class="btn btn-secondary mb-3">Kembali ke Daftar Formulir</a>

    <div class="row">
        <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Daftar Pertanyaan</h3>
                {{-- Tombol Tambah Pertanyaan --}}
                <a href="{{ route('questions.create', ['form_id' => $form->id]) }}" class="btn btn-primary btn-sm">Tambah Pertanyaan</a>
            </div>
            
            @if ($questions->isEmpty())
                <p>Belum ada pertanyaan untuk formulir ini.</p>
            @else
                <ul class="list-group">
                    @foreach ($questions as $question)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $question->question_text }}</strong> 
                                (Tipe: {{ $question->question_type }})
                                @if ($question->required)
                                    <span class="badge bg-danger">Wajib</span>
                                @endif
                            </div>
                            <div class="d-flex gap-1">
                                {{-- Tombol Edit Pertanyaan --}}
                                <a href="{{ route('questions.edit', ['question' => $question->id, 'form_id' => $form->id]) }}" class="btn btn-warning btn-sm">Edit</a>
                                {{-- Tombol Hapus Pertanyaan (Opsional, jika mau bisa ditambahkan) --}}
                                <form action="{{ route('questions.destroy', $question->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus pertanyaan ini? Semua jawaban terkait akan ikut terhapus.')">Hapus</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="col-md-6">
            <h3>Daftar Responden (Jumlah: {{ $responses->count() }})</h3>
            @if ($responses->isEmpty())
                <p>Belum ada responden untuk formulir ini.</p>
            @else
                <ul class="list-group">
                    @foreach ($responses as $responseItem) {{-- Ubah nama variabel untuk menghindari konflik dengan $response utama --}}
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $responseItem->student->name ?? 'Siswa Tidak Ditemukan' }} ({{ $responseItem->student->email ?? 'N/A' }})
                            <a href="{{ route('responses.show', $responseItem->id) }}" class="btn btn-sm btn-primary">Lihat Jawaban</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection