    @extends('layouts.app')

    @section('title', 'Edit Respon')

    @section('content')
    <div class="container mt-4">
        <h2>Edit Respon</h2>
        <form action="{{ route('responses.update', $response->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Foto --}}
            <div class="mb-3">
                <label for="photo_path" class="form-label">Foto Baru (Opsional)</label><br>
                @if ($response->photo_path)
                    <img src="{{ asset('storage/' . $response->photo_path) }}" alt="Foto Sebelumnya" style="max-width: 200px;"><br>
                @endif
                <input type="file" name="photo_path" id="photo_path" class="form-control mt-2">
            </div>

            {{-- Latitude --}}
            <div class="mb-3">
                <label for="latitude" class="form-label">Latitude</label>
                <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $response->latitude) }}" class="form-control">
            </div>

            {{-- Longitude --}}
            <div class="mb-3">
                <label for="longitude" class="form-label">Longitude</label>
                <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $response->longitude) }}" class="form-control">
            </div>

            {{-- Alamat (Opsional) --}}
            <div class="mb-3">
                <label for="formatted_address" class="form-label">Alamat (Opsional)</label>
                <input type="text" name="formatted_address" id="formatted_address" value="{{ old('formatted_address', $response->formatted_address) }}" class="form-control">
            </div>

            {{-- Validasi Lokasi --}}
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_location_valid" id="is_location_valid" value="1" {{ $response->is_location_valid ? 'checked' : '' }}>
                <label class="form-check-label" for="is_location_valid">
                    Lokasi Valid
                </label>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('responses.detail_by_form', $response->form->id) }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
    @endsection
