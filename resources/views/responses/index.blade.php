@extends('layouts.app')

@section('title', 'Lihat Jawaban')

@section('content')
<div class="container mt-4">
    <h2>Lihat Jawaban User</h2>

    <form method="GET" action="{{ route('responses.index') }}" class="mb-3">
        <div class="input-group">
            <select name="form_id" class="form-select">
                <option value="">Pilih Formulir</option>
                @foreach($forms as $form) {{-- Menggunakan $forms yang dilewatkan dari controller --}}
                    <option value="{{ $form->id }}" {{ request('form_id') == $form->id ? 'selected' : '' }}>
                        {{ $form->title }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>ID Formulir</th>
                <th>Judul Formulir</th>
                <th>Jumlah Responden</th> {{-- Ubah dari Total Jawaban --}}
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($responsesSummary as $responseSummary)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $responseSummary->form_id }}</td>
                <td>{{ $responseSummary->form->title }}</td>
                <td>{{ $responseSummary->total_responses }}</td> {{-- Menampilkan jumlah responden --}}
                <td>
                    {{-- Tombol Lihat Detail akan mengarah ke daftar jawaban per formulir --}}
                    {{-- Kita akan membuat route baru untuk ini, misal responses.detail_by_form --}}
                    <a href="{{ route('responses.detail_by_form', $responseSummary->form_id) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada respons yang tercatat.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection