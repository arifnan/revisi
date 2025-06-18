@extends('layouts.app')

@section('title', 'Detail Jawaban')

@section('content')
<div class="container mt-4">
    <h2>Detail Jawaban</h2>
    <a href="{{ route('responses.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Respon ID: {{ $response->id }}</h5>
            <p class="card-text">Formulir: <strong>{{ $response->form->title }}</strong></p>
        </div>
    </div>

    <h4 class="mt-4">Jawaban Pengguna:</h4>
    @foreach($response->answers as $answer)
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">{{ $answer->question->question_text }}</h5>
                
                @if($answer->answer_text)
                    <p><strong>Jawaban:</strong> {{ $answer->answer_text }}</p>
                @endif

                @if($answer->option_id)
                    <p><strong>Pilihan:</strong> {{ $answer->option->option_text }}</p>
                @endif

                @if($answer->file_url)
                    <p><strong>Foto Jawaban:</strong></p>
                    <img src="{{ asset($answer->file_url) }}" alt="Jawaban Gambar" width="200">
                @endif

                @if($answer->latitude && $answer->longitude)
                    <p><strong>Lokasi:</strong> {{ $answer->formatted_address }}</p>
                    <p><strong>Koordinat:</strong> {{ $answer->latitude }}, {{ $answer->longitude }}</p>
                    <iframe
                        width="100%"
                        height="250"
                        style="border:0"
                        loading="lazy"
                        allowfullscreen
                        src="https://www.google.com/maps/embed/v1/view?key=YOUR_GOOGLE_MAPS_API_KEY&center={{ $answer->latitude }},{{ $answer->longitude }}&zoom=15">
                    </iframe>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
