<?php

namespace App\Http\Controllers;


use App\Models\Response;
use Illuminate\Http\Request;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ResponsesExport;
class ResponseExportController extends Controller
{
    public function exportPdf()
    {
        $responses = Response::with(['form', 'answers.question'])->get();
        $pdf = PDF::loadView('exports.responses-pdf', compact('responses'));
        return $pdf->download('jawaban_formulir.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new ResponsesExport, 'jawaban_formulir.xlsx');
    }
}
