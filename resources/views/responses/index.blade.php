@extends('layouts.app')

@section('title', 'Daftar Respon Formulir')

@section('content')
<div class="container mt-4">
    <h2>Daftar Respon Formulir</h2>

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

    <div class="d-flex justify-content-between align-items-center mb-3">
        {{-- Dropdown untuk filter berdasarkan form_id --}}
        <form action="{{ route('responses.index') }}" method="GET" class="d-flex align-items-center">
            <label for="form_id" class="me-2 mb-0">Filter Formulir:</label>
            <select name="form_id" id="form_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Formulir</option>
                @foreach($forms as $form)
                    <option value="{{ $form->id }}" {{ request('form_id') == $form->id ? 'selected' : '' }}>
                        {{ $form->title }} ({{ $form->form_code }})
                    </option>
                @endforeach
            </select>
        </form>

        <div>
            {{-- Tombol Export (sudah ada) --}}
            <div class="dropdown d-inline-block">
                <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Export Jawaban
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="{{ route('responses.export.pdf') }}">Export PDF</a></li>
                    <li><a class="dropdown-item" href="{{ route('responses.export.excel') }}">Export Excel</a></li>
                </ul>
            </div>

            {{-- **TAMBAHKAN TOMBOL IMPORT INI** --}}
            <a href="{{ route('responses.import.form') }}" class="btn btn-primary btn-sm ms-2">Import Jawaban</a>
        </div>
    </div>

    @if ($responsesSummary->isEmpty())
        <p>Belum ada respons yang tercatat.</p>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Formulir</th>
                    <th>Guru Penanggung Jawab</th>
                    <th>Jumlah Responden</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($responsesSummary as $summary)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $summary->form->title ?? 'N/A' }}</td>
                        <td>{{ $summary->form->teacher->name ?? 'N/A' }}</td>
                        <td>{{ $summary->total_responses }}</td>
                        <td>
                            <a href="{{ route('responses.detail_by_form', $summary->form->id) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                            {{-- Jika Anda ingin opsi hapus ringkasan per form, tambahkan di sini --}}
                            {{-- <form action="{{ route('responses.destroy_summary', $summary->form->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus semua respon untuk formulir ini?')">Hapus Semua</button>
                            </form> --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection