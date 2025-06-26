<?php

namespace App\Imports;

use App\Models\Form;
use App\Models\Question;
use App\Models\Response;
use App\Models\ResponseAnswer;
use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsOnError;

class ResponsesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError
{
    private $errors = [];
    private $formId; // <-- Tambahkan properti ini

    public function __construct(int $formId) // <-- Tambahkan constructor ini
    {
        $this->formId = $formId;
    }

    public function collection(Collection $rows)
    {
        // Mengelompokkan baris berdasarkan kombinasi unik untuk mengidentifikasi satu respons penuh
        $groupedResponses = $rows->groupBy(function ($row) {
            return ($row['student_email'] ?? '') . '-' . ($row['form_code'] ?? '') . '-' . ($row['submitted_at'] ?? '');
        });

        foreach ($groupedResponses as $uniqueResponseKey => $responseRows) {
            DB::beginTransaction();
            try {
                $firstRow = $responseRows->first();

                // Validasi data penting dari baris pertama
                if (empty($firstRow['student_email']) || empty($firstRow['form_code']) || empty($firstRow['submitted_at'])) {
                     throw new \Exception("Data pokok (email siswa, kode formulir, waktu submit) tidak lengkap pada baris pertama grup ini. Baris Excel: " . ($firstRow->row() ?? 'N/A'));
                }

                // Pastikan form_code di Excel sesuai dengan form yang sedang di-import
                $currentFormCode = Form::find($this->formId)->form_code ?? null;
                if ($firstRow['form_code'] !== $currentFormCode) {
                    throw new \Exception("Kode formulir '{$firstRow['form_code']}' di Excel tidak cocok dengan formulir yang sedang di-import ('{$currentFormCode}').");
                }

                $student = Student::where('email', $firstRow['student_email'])->first();
                if (!$student) {
                    throw new \Exception("Siswa dengan email '{$firstRow['student_email']}' tidak ditemukan. Baris: " . ($firstRow->row() ?? 'N/A'));
                }

                $form = Form::find($this->formId); // Gunakan formId dari constructor
                if (!$form) {
                    throw new \Exception("Formulir dengan ID '{$this->formId}' tidak ditemukan. Ini adalah kesalahan sistem.");
                }
                
                $submittedAt = Carbon::parse($firstRow['submitted_at']);
                
                $response = Response::firstOrCreate(
                    [
                        'form_id' => $form->id,
                        'student_id' => $student->id,
                        'submitted_at' => $submittedAt,
                    ],
                    [
                        'photo_path' => $firstRow['photo_url'] ?? null,
                        'latitude' => $firstRow['latitude'] ?? null,
                        'longitude' => $firstRow['longitude'] ?? null,
                        'is_location_valid' => $this->validateLocation($firstRow['latitude'], $firstRow['longitude']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                if (!$response->wasRecentlyCreated) {
                    $changed = false;
                    if (empty($response->photo_path) && !empty($firstRow['photo_url'])) {
                        $response->photo_path = $firstRow['photo_url'];
                        $changed = true;
                    }
                    if (empty($response->latitude) && !empty($firstRow['latitude']) && empty($response->longitude) && !empty($firstRow['longitude'])) {
                        $response->latitude = $firstRow['latitude'];
                        $response->longitude = $firstRow['longitude'];
                        $response->is_location_valid = $this->validateLocation($firstRow['latitude'], $firstRow['longitude']);
                        $changed = true;
                    }
                    if ($changed) {
                        $response->save();
                    }
                }

                foreach ($responseRows as $row) {
                    $question = Question::where('form_id', $form->id) // Filter pertanyaan berdasarkan form ID
                                        ->where('question_text', $row['question_text'] ?? null)
                                        ->first();
                    if (!$question) {
                        $this->errors[] = new Failure(
                            $row->row(),
                            'question_text',
                            ["Pertanyaan '{$row['question_text']}' tidak ditemukan untuk formulir '{$form->form_code}'."],
                            $row->toArray()
                        );
                        continue;
                    }

                    $optionId = null;
                    if (!empty($row['option_text'])) {
                        $option = $question->options()->where('option_text', $row['option_text'])->first();
                        if ($option) {
                            $optionId = $option->id;
                        } else {
                            $this->errors[] = new Failure(
                                $row->row(),
                                'option_text',
                                ["Opsi '{$row['option_text']}' tidak ditemukan untuk pertanyaan '{$row['question_text']}'."],
                                $row->toArray()
                            );
                        }
                    }

                    ResponseAnswer::updateOrCreate(
                        [
                            'response_id' => $response->id,
                            'question_id' => $question->id,
                        ],
                        [
                            'answer_text' => $row['answer_text'] ?? null,
                            'option_id' => $optionId,
                            'file_url' => $row['file_url'] ?? null,
                        ]
                    );
                }

                DB::commit();

            } catch (ValidationException $e) {
                DB::rollBack();
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $this->errors[] = new Failure(
                            $firstRow->row(),
                            $field,
                            [$message],
                            $firstRow->toArray()
                        );
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = new Failure(
                    $firstRow->row(),
                    'general',
                    ["Gagal mengimpor respon ({$uniqueResponseKey}) dari baris " . ($firstRow->row() ?? 'N/A') . ": " . $e->getMessage()],
                    $firstRow->toArray()
                );
            }
        }
    }

    public function rules(): array
    {
        return [
            'form_code' => 'required|string|exists:forms,form_code',
            'student_email' => 'required|email|exists:students,email',
            'submitted_at' => 'required|date_format:Y-m-d H:i:s',
            'photo_url' => 'nullable|url|max:2048',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'question_text' => 'required|string',
            'answer_text' => 'nullable|string',
            'option_text' => 'nullable|string',
            'file_url' => 'nullable|string|max:2048',
            'formatted_address' => 'nullable|string|max:255',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'form_code.required' => 'Kolom "form_code" wajib diisi.',
            'form_code.exists' => 'Kode formulir tidak ditemukan.',
            'student_email.required' => 'Kolom "student_email" wajib diisi.',
            'student_email.email' => 'Format email siswa tidak valid.',
            'student_email.exists' => 'Email siswa tidak ditemukan.',
            'submitted_at.required' => 'Kolom "submitted_at" wajib diisi.',
            'submitted_at.date_format' => 'Format "submitted_at" harus YYYY-MM-DD HH:MM:SS.',
            'question_text.required' => 'Kolom "question_text" wajib diisi.',
        ];
    }

    protected function validateLocation($latitude, $longitude): bool
    {
        return is_numeric($latitude) && is_numeric($longitude);
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = $failure;
        }
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = new Failure(
            0,
            'general_error',
            ['Terjadi kesalahan umum saat mengimpor: ' . $e->getMessage()],
            []
        );
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}