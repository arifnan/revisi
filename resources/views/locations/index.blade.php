@extends('layouts.app')

@section('title', 'Kelola Lokasi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-dark">Kelola Lokasi</h1>
        <a href="{{ route('locations.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Lokasi
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white fw-semibold">
            <i class="bi bi-geo-alt-fill me-2"></i> Daftar Lokasi
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th>Nama Lokasi</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Radius (meter)</th>
                            <th style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $location)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $location->name }}</td>
                            <td class="text-center">{{ $location->latitude }}</td>
                            <td class="text-center">{{ $location->longitude }}</td>
                            <td class="text-center">{{ $location->radius }}</td>
                            <td class="text-center">
                                
                                {{-- === TOMBOL BARU DITAMBAHKAN DI SINI === --}}
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $location->latitude }},{{ $location->longitude }}" class="btn btn-info btn-sm me-1" target="_blank" title="Lihat di Google Maps">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                {{-- === AKHIR TOMBOL BARU === --}}

                                <a href="{{ route('locations.edit', $location->id) }}" class="btn btn-warning btn-sm me-1" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form action="{{ route('locations.destroy', $location->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada lokasi yang terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection