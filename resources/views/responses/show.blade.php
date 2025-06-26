@extends('layouts.app')

@section('title', 'Detail Jawaban: ' . ($response->student->name ?? 'N/A'))

@section('content')
<div class="container mt-4">
    <h2>Detail Jawaban untuk Formulir: {{ $response->form->title }}</h2>
    <p>Responden: <strong>{{ $response->student->name ?? 'N/A' }}</strong> (Email: {{ $response->student->email ?? 'N/A' }})</p>
    <p>Waktu Respon: {{ $response->created_at->format('d M Y H:i') }}</p>
    <a href="{{ route('responses.detail_by_form', $response->form->id) }}" class="btn btn-secondary mb-3">Kembali ke Daftar Responden</a>

    {{-- Bagian untuk menampilkan Foto, Longitude, dan Latitude --}}
    <div class="card mt-3 mb-4">
        <div class="card-header">
            <h4>Informasi Tambahan Respon</h4>
        </div>
        <div class="card-body">
            @if ($response->photo_path)
                <div class="mb-3">
                    <strong>Foto Respon:</strong><br>
                    <img src="{{ $response->photo_url }}" alt="Foto Respon" class="img-fluid" style="max-width: 300px; height: auto;">
                </div>
            @endif

         {{-- Blok BARU dengan Tombol "Tampilkan Lokasi" --}}
                @if ($response->latitude && $response->longitude)
                    <div class="mb-3">
                        <strong>Koordinat Lokasi:</strong><br>
                        Latitude: {{ $response->latitude ?? 'N/A' }}<br>
                        Longitude: {{ $response->longitude ?? 'N/A' }}<br>

                        @if ($response->is_location_valid)
                            <span class="badge bg-success">Lokasi Valid</span>
                        @else
                            <span class="badge bg-warning">Lokasi Tidak Valid</span>
                        @endif

                        @if ($response->formatted_address)
                            <br>Alamat: {{ $response->formatted_address }}
                        @endif

                        {{-- Tombol untuk membuka Google Maps --}}
                        <br>
                        <a href="https://www.google.com/maps?q={{ $response->latitude }},{{ $response->longitude }}" class="btn btn-info btn-sm mt-2" target="_blank" rel="noopener noreferrer">
                            Tampilkan Lokasi di Google Maps
                        </a>
                    </div>
                @endif

            @if (!$response->photo_path && (!$response->latitude || !$response->longitude))
                <p>Tidak ada informasi foto atau lokasi yang tersedia untuk respon ini.</p>
            @endif
        </div>
    </div>
    {{-- Akhir Bagian Tambahan --}}

    <div class="card mt-3">
        <div class="card-header">
            <h4>Daftar Jawaban</h4>
        </div>
        <a href="{{ route('responses.edit', $response->id) }}" class="btn btn-warning mb-3">
          <i class="bi bi-pencil-square"></i> Edit Foto & Lokasi
        </a>
        <ul class="list-group list-group-flush">
            @forelse ($response->answers as $answer)
                <li class="list-group-item">
                    <strong>{{ $answer->question->question_text ?? 'Pertanyaan Tidak Ditemukan' }}</strong>:<br>
                    {{-- Menampilkan jawaban berdasarkan tipe pertanyaan --}}
                    @if ($answer->question->question_type == 'file_upload' && $answer->file_url)
                        <a href="{{ asset('storage/' . $answer->file_url) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Lihat File</a>
                    @elseif ($answer->question->question_type == 'multiple_choice' || $answer->question->question_type == 'checkbox')
                        - {{ $answer->answer_text ?? 'Tidak ada jawaban' }}
                    @elseif ($answer->question->question_type == 'true_false')
                        - {{ $answer->answer_text == 1 ? 'True' : ($answer->answer_text == 0 ? 'False' : 'Tidak ada jawaban') }}
                    @else
                        - {{ $answer->answer_text ?? 'Tidak ada jawaban' }}
                    @endif
                </li>
            @empty
                <li class="list-group-item">Tidak ada jawaban ditemukan untuk respon ini.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection