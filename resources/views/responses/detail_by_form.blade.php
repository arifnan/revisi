@extends('layouts.app')

@section('title', 'Detail Jawaban Formulir: ' . $form->title)

@section('content')
<div class="container mt-4">
    <h2>Detail Jawaban untuk Formulir: {{ $form->title }}</h2>
    <a href="{{ route('responses.index') }}" class="btn btn-secondary mb-3">Kembali ke Daftar Respons</a>

    @forelse($responses as $response)
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Responden: {{ $response->student->name ?? 'Siswa Tidak Ditemukan' }} (ID Respon: {{ $response->id }})</h5>
            <small>Diisi pada: {{ $response->submitted_at ? $response->submitted_at->format('d M Y H:i') : '-' }}</small>
        </div>
        <div class="card-body">
            @if($response->photo_path)
                <div class="mb-3">
                    <h6>Foto Lokasi:</h6>
                    {{-- Perbaikan di sini: Tambahkan backslash sebelum Storage --}}
                    <img src="{{ \Storage::disk('public')->url($response->photo_path) }}" alt="Foto Responden" class="img-fluid" style="max-width: 300px;">
                </div>
            @endif

            @if($response->latitude && $response->longitude)
                <div class="mb-3">
                    <h6>Lokasi:</h6>
                    <p>Koordinat: {{ $response->latitude }}, {{ $response->longitude }}</p>
                    <iframe
                        width="100%"
                        height="250"
                        style="border:0"
                        loading="lazy"
                        allowfullscreen
                        {{-- Perbaikan di sini: latitude dan longitude langsung digunakan, bukan melalui Storage --}}
                        src="https://maps.google.com/maps?q={{ $response->latitude }},{{ $response->longitude }}&z=15&output=embed">
                    </iframe>
                </div>
            @endif

            <h6>Jawaban:</h6>
            <ul class="list-group list-group-flush">
                @forelse($response->answers as $answer)
                <li class="list-group-item">
                    <strong>{{ $answer->question->question_text }}:</strong> 
                    @if($answer->question->question_type === 'file_upload')
                        @if($answer->file_url)
                            {{-- Perbaikan di sini: Tambahkan backslash sebelum Storage --}}
                            <a href="{{ \Storage::disk('public')->url($answer->file_url) }}" target="_blank">Lihat File</a>
                        @else
                            - Tidak ada file -
                        @endif
                    @elseif(in_array($answer->question->question_type, ['MultipleChoice', 'Checkbox', 'LinearScale']))
                        @if($answer->option)
                            {{ $answer->option->option_text }}
                        @else
                            {{ $answer->answer_text }} {{-- Fallback jika ada custom jawaban atau LinearScale --}}
                        @endif
                    @else
                        {{ $answer->answer_text }}
                    @endif
                </li>
                @empty
                <li class="list-group-item">Tidak ada jawaban untuk responden ini.</li>
                @endforelse
            </ul>
        </div>
    </div>
    @empty
    <div class="alert alert-info">Belum ada responden untuk formulir ini.</div>
    @endforelse
</div>
@endsection
