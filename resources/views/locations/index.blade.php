@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-dark">Daftar Lokasi</h1>
        <a href="{{ route('locations.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Lokasi
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white fw-semibold">
            <i class="bi bi-geo-alt me-2"></i> Data Lokasi
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 5%;">no</th>
                            <th>Nama Lokasi</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Radius (meter)</th>
                            <th style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($locations as $index => $location)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $location->name }}</td>
                            <td class="text-center">{{ $location->latitude }}</td>
                            <td class="text-center">{{ $location->longitude }}</td>
                            <td class="text-center">{{ $location->radius }}</td>
                            <td class="text-center">
                                
                                {{-- === TOMBOL BARU DITAMBAHKAN DI SINI === --}}
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $location->latitude }},{{ $location->longitude }}" class="btn btn-sm btn-primary" target="_blank" title="Lihat di Google Maps">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="{{ route('locations.edit', $location->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('locations.destroy', $location->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $locations->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
