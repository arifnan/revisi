<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Response; 
use App\Models\ResponseAnswer;
use App\Models\Student;
use App\Models\Question; 
use App\Models\Teacher; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\FormResponseResource;
use App\Http\Resources\AnswerResource; 
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel; 
use App\Imports\ResponsesImport; 

class ResponseController extends Controller
{
    /**
     * Menampilkan daftar ringkasan respons per formulir untuk tampilan web.
     * Mengubah 'Total Jawaban' menjadi 'Jumlah Responden'.
    /**
     * Menampilkan daftar ringkasan respons per formulir untuk tampilan web.
     * Mengubah 'Total Jawaban' menjadi 'Jumlah Responden'.
     */
    public function index(Request $request)
    {
        // Ambil semua form untuk dropdown filter
        $forms = Form::all();

        // Query dasar untuk responses: menghitung total responses per form_id
        $query = Response::with('form.teacher', 'student')->select('form_id', DB::raw('count(*) as total_responses'))->groupBy('form_id');

        // Filter berdasarkan form_id jika ada di request
        if ($request->filled('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        $responsesSummary = $query->get();

        return view('responses.index', compact('responsesSummary', 'forms'));
    }

    /**
     * Menampilkan daftar responden dan jawaban untuk sebuah formulir tertentu.
     */
    public function showResponsesByForm(Form $form)
    {
        $questions = $form->questions()->orderBy('id')->get(); 
        $responses = $form->responses()->with('student')->latest()->get();

        return view('responses.detail_by_form', compact('form', 'questions', 'responses'));
    }

    /**
     * Menampilkan detail spesifik dari sebuah respons individual untuk tampilan web.
     */
    public function showResponseDetail(Response $response)
    {
        $response->load(['student', 'form.teacher', 'responseAnswers.question']); 
        return view('responses.show', compact('response'));
    }

    /**
     * Menampilkan formulir untuk menambahkan respons baru.
     */
    public function createResponse(Form $form)
    {
        $questions = $form->questions()->with('options')->get();
        $students = Student::all();

        return view('responses.create', compact('form', 'questions', 'students'));
    }

    /**
     * Menyimpan respons baru yang dibuat melalui panel admin/web.
     */
    public function storeResponse(Request $request)
    {
        $validatedData = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'student_id' => 'required|exists:students,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.option_id' => 'nullable|exists:question_options,id',
            'answers.*.file_upload' => 'nullable|file|max:4096|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);
    
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('response_photos', 'public');
        }
    
        $response = Response::create([
            'form_id' => $validatedData['form_id'],
            'student_id' => $validatedData['student_id'],
            'photo_path' => $photoPath,
            'latitude' => $validatedData['latitude'],
            'longitude' => $validatedData['longitude'],
            'is_location_valid' => ($validatedData['latitude'] !== null && $validatedData['longitude'] !== null),
            'submitted_at' => now(),
        ]);
    
        foreach ($validatedData['answers'] as $answerData) {
            $answerText = $answerData['answer_text'] ?? null;
            $optionId = $answerData['option_id'] ?? null;
            $fileUrl = null;
    
            $question = Question::find($answerData['question_id']);
            if ($question && $question->question_type === 'file_upload' && isset($answerData['file_upload'])) {
                $fileUrl = $answerData['file_upload']->store('response_files', 'public');
                $answerText = $answerData['file_upload']->getClientOriginalName();
            } else if (is_array($answerText)) { // For checkbox, combine to string
                $answerText = implode(', ', $answerText);
            }
    
            ResponseAnswer::create([
                'response_id' => $response->id,
                'question_id' => $answerData['question_id'],
                'answer_text' => $answerText,
                'option_id' => $optionId,
                'file_url' => $fileUrl,
            ]);
        }
    
        return redirect()->route('responses.detail_by_form', $validatedData['form_id'])->with('success', 'Respon berhasil ditambahkan.');
    }

    /**
     * Menghapus sebuah respons dari database.
     */
    public function destroy(Response $response)
    {
        $response->delete();
        return redirect()->route('responses.index')->with('success', 'Response deleted.');
    }


    // --- IMPORT RESPONS BARU BERDASARKAN FORM ---

    /**
     * Menampilkan formulir untuk mengunggah file impor respons untuk form tertentu.
     */
    public function showImportFormByForm(Form $form)
    {
        return view('responses.import', compact('form'));
    }

    /**
     * Mengelola upload file Excel untuk impor respons untuk form tertentu.
     */
    public function importExcelByForm(Request $request, Form $form)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            // Meneruskan form ID ke import class untuk validasi pertanyaan
            $import = new ResponsesImport($form->id); 
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors(); 
            if (!empty($errors)) {
                $errorMessages = [];
                foreach ($errors as $failure) {
                    $rowInfo = $failure->row() ? "Baris " . $failure->row() . ": " : "";
                    $attributeInfo = $failure->attribute() ? " (Kolom: " . $failure->attribute() . ")" : "";
                    $errorMessages[] = $rowInfo . implode(', ', $failure->errors()) . $attributeInfo;
                }
                return redirect()->back()->with('error', 'Beberapa data gagal diimpor:<br>' . implode('<br>', $errorMessages));
            }

            return redirect()->route('responses.detail_by_form', $form->id)->with('success', 'Data responden dan jawaban berhasil diimpor!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = []; 
            foreach ($failures as $failure) {
                $rowInfo = $failure->row() ? "Baris " . $failure->row() . ": " : "";
                $attributeInfo = $failure->attribute() ? " (Kolom: " . $failure->attribute() . ")" : "";
                $errorMessages[] = $rowInfo . implode(', ', $failure->errors()) . $attributeInfo;
            }
            return redirect()->back()->with('error', 'Gagal mengimpor data:<br>' . implode('<br>', $errorMessages));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    // --- API Methods ---

    /**
     * Mengambil semua respons untuk API.
     */
    public function apiIndex()
    {
        return response()->json(Response::with('responseAnswers')->get());
    }

    /**
     * Menyimpan respons formulir yang dikirim dari aplikasi mobile/API.
     */
    public function apiStore(Request $request)
    {
        $user = $request->user();

        // 1. Validasi Autentikasi: Pastikan user yang login adalah siswa
        if (!$user || !($user instanceof Student)) {
            return response()->json(['message' => 'Akses ditolak: Hanya siswa yang dapat mengirimkan respons.'], 403);
        }

        // 2. Validasi Input: Memeriksa semua data yang dikirim dari Android
        $validator = Validator::make($request->all(), [
            'form_id' => 'required|integer|exists:forms,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'answers' => 'required|json', 
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:4096', 
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data yang dikirim tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $form = Form::findOrFail($validatedData['form_id']);

        // 3. Proses dan Simpan File Foto
        $photoPath = $request->file('photo')->store('response_photos', 'public');

        // 4. Buat record Response di database dengan semua data yang relevan
        $formResponse = Response::create([
            'form_id' => $form->id,
            'student_id' => $user->id,
            'photo_path' => $photoPath, 
            'latitude' => $validatedData['latitude'] ?? null,
            'longitude' => $validatedData['longitude'] ?? null,
            'is_location_valid' => $request->input('is_location_valid_from_client', true),
            'submitted_at' => now(),
        ]);

        // 5. Proses dan simpan setiap jawaban dari string JSON
        $answersArray = json_decode($validatedData['answers'], true);
        if (is_array($answersArray)) {
            foreach ($answersArray as $answerData) {
                if (isset($answerData['question_id']) && array_key_exists('answer_text', $answerData)) {
                    $questionExists = Question::where('id', $answerData['question_id'])
                                             ->where('form_id', $form->id)
                                             ->exists();
                    if ($questionExists) {
                        ResponseAnswer::create([
                            'response_id' => $formResponse->id,
                            'question_id' => $answerData['question_id'],
                            'answer_text' => $answerData['answer_text'] ?? null,
                        ]);
                    }
                }
            }
        }

        // 6. Kembalikan data yang baru dibuat menggunakan API Resource
        $formResponse->load(['student', 'form.teacher', 'responseAnswers.question']); 
        return new FormResponseResource($formResponse);
    }
 
    /**
     * Mengambil respons berdasarkan form untuk API.
     */
    public function apiIndexByForm(Request $request, Form $form)
    {
        if ($request->user()->id !== $form->teacher_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $responses = Response::where('form_id', $form->id)
                                             ->with('student')
                                             ->latest('submitted_at')
                                             ->get();

        return \App\Http\Resources\FormResponseResource::collection($responses);
    }
 
    /**
     * Ini sepertinya adalah metode web yang diganti oleh showResponsesByForm.
     * Jika ini tidak digunakan lagi sebagai endpoint web, bisa dihapus.
     * Jika ini endpoint API yang lain, sesuaikan otorisasi dan responsnya.
     * Karena ada apiIndexByForm, ini kemungkinan duplikat atau metode yang tidak lagi relevan
     * untuk alur web yang baru. Dibiarkan di sini sesuai permintaan, tapi perlu diverifikasi penggunaannya.
     */
    public function indexByForm(Request $request, Form $form)
    {
        $user = $request->user();

        if (!$user instanceof Teacher || $user->id !== $form->teacher_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $responses = $form->responses()
                            ->with(['student', 'responseAnswers.question'])
                            ->orderBy('created_at', 'desc')
                            ->get();

        if ($responses->isEmpty()) {
            return response()->json(['message' => 'Belum ada siswa yang mengisi formulir ini.'], 200);
        }
        
        return FormResponseResource::collection($responses);
    }
 
    /**
     * Menampilkan detail spesifik dari sebuah respons, termasuk foto dan lokasi (untuk API).
     * Ini adalah endpoint untuk fitur "lihat detail riwayat".
     */
    public function apiShowResponseDetail(Request $request, Response $response)
    {
        $user = $request->user();

        $isOwner = ($user instanceof Student && $user->id === $response->student_id);
        $isTeacherOfForm = ($user instanceof Teacher && $response->form && $user->id === $response->form->teacher_id);

        if (!$isOwner && !$isTeacherOfForm) { 
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $response->load(['student', 'form.teacher', 'responseAnswers.question']);

        return new FormResponseResource($response);
    }

    /**
     * Menghapus respons melalui API.
     */
    public function apiDestroy(Response $response)
    {
        $response->delete();
        return response()->json(['message' => 'Response deleted']);
    }
}