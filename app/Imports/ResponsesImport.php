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

class ResponsesImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $errors = [];

    public function collection(Collection $rows)
    {
        // Mengelompokkan baris berdasarkan kombinasi unik untuk mengidentifikasi satu respons penuh
        // Menggunakan student_email, form_code, dan submitted_at sebagai identifikasi unik respons
        $groupedResponses = $rows->groupBy(function ($row) {
            // Pastikan kunci-kunci ini ada dan digabungkan menjadi string unik
            return $row['student_email'] . '-' . $row['form_code'] . '-' . $row['submitted_at'];
        });

        foreach ($groupedResponses as $uniqueResponseKey => $responseRows) {
            DB::beginTransaction();
            try {
                $firstRow = $responseRows->first();

                // 1. Dapatkan Student
                $student = Student::where('email', $firstRow['student_email'])->first();
                if (!$student) {
                    throw new \Exception("Siswa dengan email '{$firstRow['student_email']}' tidak ditemukan.");
                }

                // 2. Dapatkan Form
                $form = Form::where('form_code', $firstRow['form_code'])->first();
                if (!$form) {
                    throw new \Exception("Formulir dengan kode '{$firstRow['form_code']}' tidak ditemukan.");
                }

                // 3. Buat atau Temukan Respons Utama
                // Cek apakah respon sudah ada (berdasarkan form_id, student_id, dan submitted_at)
                $submittedAt = Carbon::parse($firstRow['submitted_at']);
                
                $response = Response::firstOrCreate(
                    [
                        'form_id' => $form->id,
                        'student_id' => $student->id,
                        'submitted_at' => $submittedAt,
                    ],
                    [
                        'photo_path' => $firstRow['photo_url'] ?? null, // Simpan URL gambar
                        'latitude' => $firstRow['latitude'] ?? null,
                        'longitude' => $firstRow['longitude'] ?? null,
                        'is_location_valid' => $this->validateLocation($firstRow['latitude'], $firstRow['longitude']),
                        'created_at' => now(), // Set created_at dan updated_at
                        'updated_at' => now(),
                    ]
                );

                // Update photo_path/location jika respons sudah ada tapi data ini belum ada
                if (!$response->wasRecentlyCreated) {
                    if (empty($response->photo_path) && !empty($firstRow['photo_url'])) {
                        $response->photo_path = $firstRow['photo_url'];
                    }
                    if (empty($response->latitude) && !empty($firstRow['latitude'])) {
                        $response->latitude = $firstRow['latitude'];
                        $response->longitude = $firstRow['longitude'];
                        $response->is_location_valid = $this->validateLocation($firstRow['latitude'], $firstRow['longitude']);
                    }
                    $response->save();
                }

                // 4. Proses Setiap Jawaban
                foreach ($responseRows as $row) {
                    // Dapatkan Pertanyaan
                    $question = Question::where('form_id', $form->id)
                                        ->where('question_text', $row['question_text'])
                                        ->first();
                    if (!$question) {
                        // Log error atau lewati jika pertanyaan tidak ditemukan
                        // Ini akan ditangkap oleh validasi di rules(), tapi ini untuk logika bisnis
                        continue;
                    }

                    $optionId = null;
                    if (!empty($row['option_text'])) {
                        $option = $question->options()->where('option_text', $row['option_text'])->first();
                        if ($option) {
                            $optionId = $option->id;
                        } else {
                            // Jika opsi tidak ditemukan, mungkin ada typo atau opsi baru
                            // Anda bisa membuat opsi baru atau mencatat error
                        }
                    }

                    // Buat atau perbarui ResponseAnswer
                    ResponseAnswer::updateOrCreate(
                        [
                            'response_id' => $response->id,
                            'question_id' => $question->id,
                        ],
                        [
                            'answer_text' => $row['answer_text'] ?? null,
                            'option_id' => $optionId,
                            'file_url' => $row['file_url'] ?? null, // Jika ada kolom file_url di response_answers
                            'latitude' => $row['latitude'] ?? null, // Jika ada kolom latitude di response_answers
                            'longitude' => $row['longitude'] ?? null, // Jika ada kolom longitude di response_answers
                            'formatted_address' => $row['formatted_address'] ?? null, // Jika ada kolom formatted_address di response_answers
                        ]
                    );
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = new Failure(
                    $firstRow->row, // Gunakan nomor baris dari baris pertama dalam grup
                    'general',
                    ["Gagal mengimpor respon ({$uniqueResponseKey}): " . $e->getMessage()],
                    []
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
            'photo_url' => 'nullable|url|max:2048', // URL gambar
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'question_text' => 'required|string',
            'answer_text' => 'nullable|string',
            'option_text' => 'nullable|string',
            // Pastikan kolom lain yang Anda butuhkan juga divalidasi
            'file_url' => 'nullable|string|max:2048', // Untuk jawaban berupa file_upload
            'formatted_address' => 'nullable|string|max:255',
        ];
    }

    // Metode validasi lokasi sederhana
    protected function validateLocation($latitude, $longitude): bool
    {
        // Lokasi dianggap valid jika kedua nilai ada dan numerik
        return is_numeric($latitude) && is_numeric($longitude);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}