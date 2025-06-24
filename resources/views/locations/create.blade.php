@extends('layouts.app')

@section('title', 'Tambah Lokasi')

@section('content')
<div class="container mt-4">
    <h2>Tambah Lokasi Baru</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('locations.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nama Lokasi</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label for="latitude" class="form-label">Latitude</label>
            <input type="text" name="latitude" id="latitude" class="form-control" value="{{ old('latitude') }}" placeholder="-6.200000" required>
        </div>
        <div class="mb-3">
            <label for="longitude" class="form-label">Longitude</label>
            <input type="text" name="longitude" id="longitude" class="form-control" value="{{ old('longitude') }}" placeholder="106.816666" required>
        </div>
        <div class="mb-3">
            <label for="radius" class="form-label">Radius (dalam meter)</label>
            <input type="number" name="radius" id="radius" class="form-control" value="{{ old('radius') }}" placeholder="100" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('locations.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection