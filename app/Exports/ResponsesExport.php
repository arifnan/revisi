<?php

namespace App\Exports;

use App\Models\Response;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ResponsesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Response::with('form')->get()->map(function ($response) {
            return [
                'ID Respon' => $response->id,
                'Judul Form' => $response->form->title ?? '-',
                'Total Jawaban' => $response->answers->count()
            ];
        });
    }

    public function headings(): array
    {
        return ['ID Respon', 'Judul Form', 'Total Jawaban'];
    }
}
