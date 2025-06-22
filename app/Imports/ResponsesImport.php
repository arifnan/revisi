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
use Illuminate\Support\Str; // <-- SOLUSI 1: Tambahkan ini untuk menggunakan Str facade
use Maatwebsite\Excel\Concerns\SkipsOnFailure; // <-- SOLUSI 2: Tambahkan interface ini
use Maatwebsite\Excel\Concerns\SkipsOnError;   // <-- SOLUSI 2: Tambahkan interface ini

class ResponsesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError // <-- SOLUSI 2: Implementasikan interface
{
    private $errors = [];

    public function collection(Collection $rows)
    {
        // Mengelompokkan baris berdasarkan kombinasi unik untuk mengidentifikasi satu respons penuh
        // Menggunakan student_email, form_code, dan submitted_at sebagai identifikasi unik respons
        $groupedResponses = $rows->groupBy(function ($row) {
            // Pastikan kunci-kunci ini ada dan digabungkan menjadi string unik
            return ($row['student_email'] ?? '') . '-' . ($row['form_code'] ?? '') . '-' . ($row['submitted_at'] ?? '');
        });

        foreach ($groupedResponses as $uniqueResponseKey => $responseRows) {
            DB::beginTransaction();
            try {
                $firstRow = $responseRows->first();

                // Validasi data penting dari baris pertama
                if (empty($firstRow['student_email']) || empty($firstRow['form_code']) || empty($firstRow['submitted_at'])) {
                     throw new \Exception("Data pokok (email siswa, kode formulir, waktu submit) tidak lengkap pada baris pertama grup ini.");
                }

                // 1. Dapatkan Student
                $student = Student::where('email', $firstRow['student_email'])->first();
                if (!$student) {
                    throw new \Exception("Siswa dengan email '{$firstRow['student_email']}' tidak ditemukan. Baris: " . ($firstRow->row ?? 'N/A'));
                }

                // 2. Dapatkan Form
                $form = Form::where('form_code', $firstRow['form_code'])->first();
                if (!$form) {
                    throw new \Exception("Formulir dengan kode '{$firstRow['form_code']}' tidak ditemukan. Baris: " . ($firstRow->row ?? 'N/A'));
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

                // 4. Proses Setiap Jawaban dari BARIS-BARIS DALAM GRUP
                foreach ($responseRows as $row) {
                    // Dapatkan Pertanyaan berdasarkan form_id dan question_text dari baris saat ini
                    $question = Question::where('form_id', $form->id)
                                        ->where('question_text', $row['question_text'] ?? null) // Pastikan question_text ada
                                        ->first();
                    if (!$question) {
                        // Jika pertanyaan tidak ditemukan, tambahkan sebagai kegagalan
                        $this->errors[] = new Failure(
                            $row->row(), // <-- SOLUSI 3: Gunakan row() untuk nomor baris
                            'question_text',
                            ["Pertanyaan '{$row['question_text']}' tidak ditemukan untuk formulir '{$form->form_code}'."],
                            $row->toArray()
                        );
                        continue; // Lanjutkan ke baris berikutnya
                    }

                    $optionId = null;
                    if (!empty($row['option_text'])) {
                        $option = $question->options()->where('option_text', $row['option_text'])->first();
                        if ($option) {
                            $optionId = $option->id;
                        } else {
                            // Jika opsi tidak ditemukan, tambahkan sebagai kegagalan
                            $this->errors[] = new Failure(
                                $row->row(), // <-- SOLUSI 3
                                'option_text',
                                ["Opsi '{$row['option_text']}' tidak ditemukan untuk pertanyaan '{$row['question_text']}'."],
                                $row->toArray()
                            );
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
                            'file_url' => $row['file_url'] ?? null,
                            // Anda tidak boleh menyimpan latitude/longitude/address di ResponseAnswer
                            // kecuali jika pertanyaan itu sendiri adalah pertanyaan lokasi spesifik.
                            // Data lokasi umumnya disimpan di model Response utama.
                            // Jika memang ada pertanyaan lokasi terpisah, maka kolom ini mungkin relevan.
                            // 'latitude' => $row['latitude'] ?? null, 
                            // 'longitude' => $row['longitude'] ?? null, 
                            // 'formatted_address' => $row['formatted_address'] ?? null,
                        ]
                    );
                }

                DB::commit();

            } catch (ValidationException $e) {
                DB::rollBack();
                // Kegagalan validasi dari rule() akan ditangkap oleh SkipsOnFailure
                // Ini untuk kegagalan validasi custom di dalam collection()
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $this->errors[] = new Failure(
                            $firstRow->row(), // <-- SOLUSI 3
                            $field,
                            [$message],
                            $firstRow->toArray()
                        );
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = new Failure(
                    $firstRow->row(), // <-- SOLUSI 3: Gunakan row()
                    'general', // <-- SOLUSI 4: Pastikan ini string
                    ["Gagal mengimpor respon ({$uniqueResponseKey}) dari baris " . ($firstRow->row() ?? 'N/A') . ": " . $e->getMessage()],
                    $firstRow->toArray() // Berikan seluruh baris untuk konteks
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
            'question_text' => 'required|string', // Pastikan setiap baris memiliki teks pertanyaan
            'answer_text' => 'nullable|string',
            'option_text' => 'nullable|string',
            'file_url' => 'nullable|string|max:2048', // Untuk jawaban berupa file_upload
            'formatted_address' => 'nullable|string|max:255',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'form_code.required' => 'Kolom "kode_formulir" wajib diisi.',
            'form_code.exists' => 'Kode formulir tidak ditemukan.',
            'student_email.required' => 'Kolom "student_email" wajib diisi.',
            'student_email.email' => 'Format email siswa tidak valid.',
            'student_email.exists' => 'Email siswa tidak ditemukan.',
            'submitted_at.required' => 'Kolom "submitted_at" wajib diisi.',
            'submitted_at.date_format' => 'Format "submitted_at" harus YYYY-MM-DD HH:MM:SS.',
            'question_text.required' => 'Kolom "question_text" wajib diisi.',
            // Tambahkan pesan untuk aturan lain jika perlu
        ];
    }

    // Metode validasi lokasi sederhana
    protected function validateLocation($latitude, $longitude): bool
    {
        // Lokasi dianggap valid jika kedua nilai ada dan numerik
        return is_numeric($latitude) && is_numeric($longitude);
    }

    // <-- SOLUSI 2: Tambahkan metode ini untuk SkipsOnFailure
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = $failure;
        }
    }

    // <-- SOLUSI 2: Tambahkan metode ini untuk SkipsOnError
    public function onError(\Throwable $e)
    {
        // Tangani kesalahan umum yang tidak tertangkap oleh validasi baris
        $this->errors[] = new Failure(
            0, // Gunakan 0 atau nomor baris yang relevan jika bisa diidentifikasi
            'general_error', // <-- SOLUSI 4: Pastikan ini string
            ['Terjadi kesalahan umum saat mengimpor: ' . $e->getMessage()],
            []
        );
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}