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
use Illuminate\Support\Facades\DB; // Tambahkan ini untuk DB Facade

class ResponseController extends Controller
{
    /**
     * Menampilkan daftar ringkasan respons per formulir untuk tampilan web.
     * Mengubah 'Total Jawaban' menjadi 'Jumlah Responden'.
     */
    public function index(Request $request)
    {
        // Ambil semua form untuk dropdown filter
        $forms = Form::all();

        // Query dasar untuk responses: menghitung total responses per form_id
        $query = Response::with('form', 'student')->select('form_id', DB::raw('count(*) as total_responses'))->groupBy('form_id');

        // Filter berdasarkan form_id jika ada di request
        if ($request->filled('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        $responsesSummary = $query->get();

        return view('responses.index', compact('responsesSummary', 'forms'));
    }

    /**
     * Menampilkan daftar responden dan jawaban untuk sebuah formulir tertentu.
     * Ini adalah target dari tombol "Lihat Detail" di halaman index respons.
     */
    public function showResponsesByForm(Form $form)
    {
        // Ambil semua respons untuk formulir ini, termasuk student dan answers
        $responses = $form->responses()->with(['student', 'answers.question.options'])->get();

        return view('responses.detail_by_form', compact('form', 'responses'));
    }

    /**
     * Menampilkan detail spesifik dari sebuah respons individual untuk tampilan web.
     * Ini digunakan jika ada rute 'responses.show' yang mengarah ke detail 1 respon.
     */
    public function showResponseDetail(Response $response)
    {
        // Eager load semua relasi yang dibutuhkan untuk tampilan detail
        $response->load(['student', 'form.teacher', 'answers.question.options']);
        return view('responses.show', compact('response'));
    }


    /**
     * Menghapus sebuah respons dari database.
     */
    public function destroy(Response $response)
    {
        $response->delete();
        return redirect()->route('responses.index')->with('success', 'Response deleted.');
    }

    // --- API Methods ---

    /**
     * Mengambil semua respons untuk API.
     */
    public function apiIndex()
    {
        return response()->json(Response::with('answers')->get());
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
            'answers' => 'required|json', // Android mengirim string JSON
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:4096', // Foto wajib, maks 4MB
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
            'photo_path' => $photoPath, // Simpan path relatif ke foto
            'latitude' => $validatedData['latitude'] ?? null,
            'longitude' => $validatedData['longitude'] ?? null,
            // Asumsi validasi lokasi dilakukan di client, atau bisa ditambahkan logika server di sini
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
        $formResponse->load(['student', 'form.teacher', 'answers.question']);
        return new FormResponseResource($formResponse);
    }
  
    /**
     * Mengambil respons berdasarkan form untuk API.
     */
    public function apiIndexByForm(Request $request, Form $form)
	{
        // Logika untuk otorisasi dan mengambil data...
        // Contoh:
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

        // Autorisasi: Pastikan guru yang meminta adalah pemilik formulir
        if (!$user instanceof Teacher || $user->id !== $form->teacher_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Ambil semua response untuk form ini, beserta relasi yang diperlukan
        $responses = $form->responses()
                          ->with(['student', 'answers.question'])
                          ->orderBy('created_at', 'desc') // Menggunakan created_at untuk konsistensi
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

        // Logika otorisasi (opsional tapi sangat disarankan)
        $isOwner = ($user instanceof Student && $user->id === $response->student_id);
        $isTeacherOfForm = ($user instanceof Teacher && $user->id === $response->form->teacher_id);

        if (!$isOwner && (!$isTeacherOfForm || !$response->form)) { // Tambahkan pengecekan $response->form
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Eager load semua relasi yang dibutuhkan oleh resource
        $response->load(['student', 'form.teacher', 'answers.question.options']);

        // Kembalikan data menggunakan resource untuk konsistensi format
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
