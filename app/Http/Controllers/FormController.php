<?php

namespace App\Http\Controllers; // Sesuaikan dengan namespace Anda

use App\Models\Form;
use App\Models\Question;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Diperlukan untuk Str::random
use App\Http\Resources\FormResource;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang terautentikasi
use Illuminate\Validation\Rule;

class FormController extends Controller
{
    // Metode untuk Web (blade views)
    public function index(Request $request) // Tambahkan Request $request
    {
        $user = Auth::user();
        $forms = collect(); // Inisialisasi koleksi kosong

        if ($user && $user instanceof Teacher) {
            $forms = Form::where('teacher_id', $user->id)->with('teacher')->latest()->get();
            $teachers = Teacher::where('id', $user->id)->get(); // Hanya guru yang login
        } else {
            // Jika admin atau peran lain yang bisa melihat semua form
            $forms = Form::with('teacher')->latest()->get();
            $teachers = Teacher::all(); // Untuk admin, tampilkan semua guru untuk filter
        }
        
        // Filter formulir berdasarkan teacher_id jika ada dalam request
        if ($request->has('teacher_id') && $request->teacher_id !== '') {
            $forms = $forms->where('teacher_id', $request->teacher_id);
        }

        return view('forms.index', compact('forms', 'teachers')); // Tambahkan 'teachers' di compact
    }


    public function create()
    {
        // Hanya guru yang login yang bisa membuat form dari sisi web
        $teachers = []; // Default ke array kosong
        $authUser = Auth::user();

        if ($authUser instanceof Teacher) {
             $teachers = Teacher::where('id', $authUser->id)->get();
        } else if ($authUser && $authUser->isAdmin()) { // Contoh jika ada method isAdmin() di model User admin
            // Untuk admin, mungkin tampilkan semua guru atau batasi
            $teachers = Teacher::all();
        }
        // Jika tidak ada guru yang login atau bukan admin, $teachers akan kosong atau
        // Anda bisa tambahkan logic untuk redirect atau error.
        return view('forms.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teacher_id' => 'required|integer|exists:teachers,id'
        ]);

        $validatedData['form_code'] = Str::upper(Str::random(8));
        $form = Form::create($validatedData);

        return redirect()->route('forms.index')->with('success', 'Form created successfully.');
    }

    public function edit(Form $form)
    {
        // $this->authorize('update', $form); // Dikomentari untuk sementara
        $teachers = Teacher::all();
        return view('forms.edit', compact('form', 'teachers'));
    }

    public function update(Request $request, Form $form)
    {
        // $this->authorize('update', $form); // Dikomentari untuk sementara
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teacher_id' => 'required|integer|exists:teachers,id'
        ]);

        $form->update($validatedData);
        return redirect()->route('forms.index')->with('success', 'Form updated successfully.');
    }

    public function destroy(Form $form)
    {
        // $this->authorize('delete', $form); // Dikomentari untuk sementara
        $form->delete();
        return redirect()->route('forms.index')->with('success', 'Form deleted successfully.');
    }

    // =====================================================
    // API methods (TETAP SAMA SEPERTI VERSI SEBELUMNYA YANG SUDAH BAIK)
    // =====================================================

   public function apiIndex(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user instanceof Teacher) { // Sesuaikan dengan model User/Teacher Anda
            return response()->json(['message' => 'Unauthorized or not a teacher.'], 403);
        }
        $forms = Form::where('teacher_id', $user->id)
                     ->with(['questions.options', 'teacher']) // Eager load questions DAN options dari questions
                     ->latest()
                     ->get();
        return FormResource::collection($forms);
    }

public function apiStore(Request $request)
{
    $user = $request->user();
    if (!$user || !$user instanceof Teacher) {
        return response()->json(['message' => 'Unauthorized: Only teachers can create forms.'], 403);
    }

    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'questions' => 'present|array',
        'questions.*.question_text' => 'required|string|max:65535',
        'questions.*.question_type' => ['required', 'string',Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale','true_false', 'file_upload'])],
        'questions.*.options' => 'nullable|array',
        'questions.*.options.*' => 'nullable|string|max:255',
        'questions.*.required' => 'required|boolean',
    ]);

    // Buat form dengan form_code yang dihasilkan secara acak
    $form = Form::create([
        'title' => $validatedData['title'],
        'description' => $validatedData['description'],
        'teacher_id' => $user->id,
        'form_code' => Str::upper(Str::random(8))
    ]);

    if (!empty($validatedData['questions'])) {
        foreach ($validatedData['questions'] as $questionData) {
            $newQuestion = $form->questions()->create([
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'required' => $questionData['required'],
            ]);

            if (in_array($questionData['question_type'], ['MultipleChoice', 'Checkbox', 'LinearScale']) && isset($questionData['options']) && is_array($questionData['options'])) {
                foreach ($questionData['options'] as $optionText) {
                    if (!empty($optionText) || $questionData['question_type'] === 'LinearScale') {
                         $newQuestion->options()->create(['option_text' => $optionText]);
                    }
                }
            }
        }
    }

    // PENTING: Muat relasi pada objek $form yang sudah ada, BUKAN menggunakan fresh()
    $form->load(['questions.options', 'teacher']);

    // Kembalikan resource dengan objek $form yang sudah lengkap
    return new FormResource($form);
}


    public function apiShow(Request $request, Form $form)
    {
        return new FormResource($form->load(['questions.options', 'teacher']));
    }

    public function apiGetByFormCode(Request $request, $form_code)
    {
        $form = Form::where('form_code', Str::upper($form_code))->with(['questions.options', 'teacher'])->first();
        if (!$form) {
            return response()->json(['message' => 'Formulir dengan kode tersebut tidak ditemukan.'], 404);
        }
        return new FormResource($form);
    }

    public function apiUpdate(Request $request, Form $form)
    {
        $user = $request->user();
        if (!$user || !$user instanceof Teacher || $user->id !== $form->teacher_id) {
            return response()->json(['message' => 'Unauthorized to update this form.'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'sometimes|present|array',
            'questions.*.id' => 'nullable|integer|exists:questions,id,form_id,'.$form->id,
            'questions.*.question_text' => 'required_with:questions.*|string|max:65535',
            'questions.*.question_type' => ['required_with:questions.*', 'string', Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale'])],
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*' => 'nullable|string|max:255',
            'questions.*.required' => 'required_with:questions.*|boolean',
        ]);

        $form->fill(array_intersect_key($validatedData, array_flip(['title', 'description'])));
        $form->save();

        if ($request->has('questions')) {
            $existingQuestionIds = $form->questions()->pluck('id')->toArray();
            $requestQuestionIds = [];

            foreach ($validatedData['questions'] as $questionData) {
                $questionId = $questionData['id'] ?? null;
                $questionPayload = [
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'required' => $questionData['required'],
                ];

                $currentQuestion = null;
                if ($questionId && in_array($questionId, $existingQuestionIds)) {
                    Question::where('id', $questionId)->where('form_id', $form->id)->update($questionPayload);
                    $currentQuestion = Question::find($questionId);
                    $requestQuestionIds[] = $questionId;
                } else {
                    $currentQuestion = $form->questions()->create($questionPayload);
                    $requestQuestionIds[] = $currentQuestion->id;
                }

                // Update atau buat options untuk pertanyaan saat ini
                if ($currentQuestion && isset($questionData['options']) && is_array($questionData['options'])) {
                    $currentQuestion->options()->delete(); // Hapus opsi lama untuk pertanyaan ini
                    foreach ($questionData['options'] as $optionText) {
                        if (!empty($optionText) || $questionData['question_type'] === 'LinearScale') {
                            $currentQuestion->options()->create(['option_text' => $optionText]);
                        }
                    }
                }
            }
            $questionsToDelete = array_diff($existingQuestionIds, $requestQuestionIds);
            if (!empty($questionsToDelete)) {
                Question::whereIn('id', $questionsToDelete)->where('form_id', $form->id)->delete();
            }
        }
        return new FormResource($form->fresh()->load(['questions.options', 'teacher']));
    }

    public function apiDestroy(Request $request, Form $form)
    {
        $user = $request->user();
        if (!$user || !$user instanceof Teacher || $user->id !== $form->teacher_id) {
            return response()->json(['message' => 'Unauthorized to delete this form.'], 403);
        }
        $form->delete(); // onDelete('cascade') di migrasi questions akan menghapus pertanyaan dan opsinya
        return response()->json(['message' => 'Form deleted successfully']);
    }
}
