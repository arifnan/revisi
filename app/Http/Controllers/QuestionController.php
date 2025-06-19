<?php

namespace App\Http\Controllers; // Sesuaikan dengan namespace Anda

use App\Models\Form;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Diperlukan untuk Str::random
use App\Http\Resources\QuestionResource; // Pastikan ini di-import
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    // Method index (jika Anda punya daftar semua pertanyaan terpisah)
    public function index()
    {
        $questions = Question::with('form')->get(); // Ambil semua pertanyaan dengan form terkait
        return view('questions.index', compact('questions'));
    }

    // Method untuk menampilkan form tambah pertanyaan baru untuk form tertentu
    public function create(Request $request)
    {
        $form_id = $request->query('form_id'); // Ambil form_id dari query parameter
        $form = Form::find($form_id); // Cari form berdasarkan ID

        if (!$form) {
            return redirect()->route('forms.index')->with('error', 'Formulir tidak ditemukan.');
        }

        return view('questions.create', compact('form'));
    }

    // Method untuk menyimpan pertanyaan baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'question_text' => 'required|string|max:65535',
            'question_type' => ['required', 'string', Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale', 'true_false', 'file_upload'])],
            'required' => 'required|boolean',
            'options' => 'nullable|array', // Hanya relevan untuk MultipleChoice/Checkbox/LinearScale
            'options.*' => 'nullable|string|max:255',
        ]);

        $form = Form::find($validatedData['form_id']);
        if (!$form) {
            return redirect()->route('forms.index')->with('error', 'Formulir tidak ditemukan.');
        }

        $question = $form->questions()->create([
            'question_text' => $validatedData['question_text'],
            'question_type' => $validatedData['question_type'],
            'required' => $validatedData['required'],
        ]);

        // Simpan opsi jika tipe pertanyaan relevan
        if (in_array($validatedData['question_type'], ['MultipleChoice', 'Checkbox', 'LinearScale']) && isset($validatedData['options']) && is_array($validatedData['options'])) {
            foreach ($validatedData['options'] as $optionText) {
                if (!empty($optionText) || $validatedData['question_type'] === 'LinearScale') {
                    $question->options()->create(['option_text' => $optionText]);
                }
            }
        }

        return redirect()->route('responses.detail_by_form', $form->id)->with('success', 'Pertanyaan berhasil ditambahkan.');
    }

    // Method untuk menampilkan form edit pertanyaan
    public function edit(Question $question, Request $request)
    {
        $form_id = $request->query('form_id');
        // Pastikan pertanyaan ini milik form yang benar jika form_id diberikan
        if ($form_id && $question->form_id != $form_id) {
            return redirect()->route('forms.index')->with('error', 'Pertanyaan tidak ditemukan pada formulir tersebut.');
        }
        $form = Form::find($question->form_id); // Ambil form untuk konteks

        $question->load('options'); // Muat opsi jika ada

        return view('questions.edit', compact('question', 'form'));
    }

    // Method untuk update pertanyaan
    public function update(Request $request, Question $question)
    {
        $validatedData = $request->validate([
            'question_text' => 'required|string|max:65535',
            'question_type' => ['required', 'string', Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale', 'true_false', 'file_upload'])],
            'required' => 'required|boolean',
            'options' => 'nullable|array', // Hanya relevan untuk MultipleChoice/Checkbox/LinearScale
            'options.*' => 'nullable|string|max:255',
        ]);

        $question->update([
            'question_text' => $validatedData['question_text'],
            'question_type' => $validatedData['question_type'],
            'required' => $validatedData['required'],
        ]);

        // Update opsi jika tipe pertanyaan relevan
        if (in_array($validatedData['question_type'], ['MultipleChoice', 'Checkbox', 'LinearScale']) && isset($validatedData['options']) && is_array($validatedData['options'])) {
            $question->options()->delete(); // Hapus semua opsi lama
            foreach ($validatedData['options'] as $optionText) {
                if (!empty($optionText) || $validatedData['question_type'] === 'LinearScale') {
                    $question->options()->create(['option_text' => $optionText]);
                }
            }
        } else {
            // Jika tipe pertanyaan berubah dan tidak lagi memerlukan opsi, hapus opsi lama
            $question->options()->delete();
        }

        return redirect()->route('responses.detail_by_form', $question->form_id)->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    // Method untuk menghapus pertanyaan
    public function destroy(Question $question)
    {
        $form_id = $question->form_id; // Ambil form_id sebelum dihapus
        $question->delete(); // onDelete('cascade') di migrasi akan menghapus opsi dan jawaban terkait

        return redirect()->route('responses.detail_by_form', $form_id)->with('success', 'Pertanyaan berhasil dihapus.');
    }

    // =====================================================
    // API methods
    // =====================================================

    public function apiIndex()
    {
        // Eager load options untuk disertakan dalam resource
        return QuestionResource::collection(Question::with(['form', 'options'])->get());
    }

    public function apiStore(Request $request)
    {
        $data = $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'question_text' => 'required|string|max:65535',
            'question_type' => ['required', 'string', Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale'])], // Sesuaikan rules ini dengan API clients Anda
            'options' => 'nullable|array',
            'options.*' => 'nullable|string|max:255',
            'required' => 'required|boolean',
        ]);

        // Buat pertanyaan utama
        $questionPayload = [
            'form_id' => $data['form_id'],
            'question_text' => $data['question_text'],
            'question_type' => $data['question_type'],
            'required' => $data['required'],
        ];
        $question = Question::create($questionPayload);

        // Buat QuestionOptions jika ada dan tipe pertanyaan mendukung
        if (in_array($data['question_type'], ['MultipleChoice', 'Checkbox', 'LinearScale']) && isset($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $optionText) {
                if (!empty($optionText) || $data['question_type'] === 'LinearScale') { // Linear scale options bisa kosong (min/max label)
                    $question->options()->create(['option_text' => $optionText]);
                }
            }
        }

        return new QuestionResource($question->load('options'));
    }

    public function apiUpdate(Request $request, Question $question)
    {
        $data = $request->validate([
            'form_id' => 'sometimes|required|integer|exists:forms,id',
            'question_text' => 'sometimes|required|string|max:65535',
            'question_type' => ['sometimes','required', 'string', Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale'])], // Sesuaikan rules ini
            'options' => 'nullable|array',
            'options.*' => 'nullable|string|max:255',
            'required' => 'sometimes|required|boolean',
        ]);

        // Update field dasar pertanyaan
        $question->update(array_intersect_key($data, array_flip(['form_id', 'question_text', 'question_type', 'required'])));

        // Update options jika ada dalam request
        if ($request->has('options')) {
            $question->options()->delete(); // Hapus opsi lama
            if (is_array($data['options'])) {
                foreach ($data['options'] as $optionText) {
                    if (!empty($optionText) || $question->question_type === 'LinearScale') {
                        $question->options()->create(['option_text' => $optionText]);
                    }
                }
            }
        }

        return new QuestionResource($question->fresh()->load('options'));
    }

    public function apiDestroy(Question $question)
    {
        $question->delete(); // Ini juga akan menghapus QuestionOptions jika onDelete cascade
        return response()->json(['message' => 'Question deleted']);
    }
}