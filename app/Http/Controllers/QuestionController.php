<?
namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Form;
use Illuminate\Http\Request;
use App\Http\Resources\QuestionResource;
use Illuminate\Validation\Rule;
class QuestionController extends Controller
{
    // Metode Web (index, create, store, edit, update, destroy)
    // Untuk metode web, Anda juga perlu menyesuaikan cara 'options' disimpan jika
    // formulir web Anda mendukung pembuatan/pengeditan opsi.
    // Fokus saat ini adalah pada API.

    public function index()
    {
        $questions = Question::with('form', 'options')->get(); // Load options untuk tampilan web
        return view('questions.index', compact('questions'));
    }

    public function create()
    {
        $forms = Form::all();
        return view('questions.create', compact('forms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'question_text' => 'required|string|max:65535',
            'question_type' => ['required', 'string', Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale'])],
            'required' => 'nullable|boolean', // Ubah is_required menjadi required
            'options_text' => 'nullable|array', // Jika opsi dikirim sebagai array teks dari form web
            'options_text.*' => 'nullable|string|max:255',
        ]);

        $data['required'] = $request->boolean('required'); // Pastikan boolean

        $question = Question::create($data);

        if ($request->has('options_text') && is_array($request->options_text)) {
            foreach ($request->options_text as $optionText) {
                if (!empty($optionText)) {
                    $question->options()->create(['option_text' => $optionText]);
                }
            }
        }
        return redirect()->route('questions.index')->with('success', 'Question created.');
    }

    public function edit(Question $question)
    {
        $forms = Form::all();
        $question->load('options'); // Load opsi yang ada untuk ditampilkan di form edit
        return view('questions.edit', compact('question', 'forms'));
    }

    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'form_id' => 'sometimes|required|integer|exists:forms,id',
            'question_text' => 'sometimes|required|string|max:65535',
            'question_type' => ['sometimes','required', 'string', Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale'])],
            'required' => 'sometimes|nullable|boolean',
            'options_text' => 'nullable|array',
            'options_text.*' => 'nullable|string|max:255',
        ]);
        if ($request->has('required')) {
            $data['required'] = $request->boolean('required');
        }

        $question->update($data);

        if ($request->has('options_text')) {
            $question->options()->delete(); // Hapus opsi lama
            if (is_array($request->options_text)) {
                foreach ($request->options_text as $optionText) {
                    if (!empty($optionText)) {
                        $question->options()->create(['option_text' => $optionText]);
                    }
                }
            }
        }
        return redirect()->route('questions.index')->with('success', 'Question updated.');
    }

     public function destroy(Question $question)
    {
        // Opsi dan jawaban terkait akan terhapus jika onDelete('cascade') diset di migrasi
        $question->delete();
        return redirect()->route('questions.index')->with('success', 'Question deleted.');
    }

    // API methods
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
            'question_type' => ['required', 'string', Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale'])],
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
            'question_type' => ['sometimes','required', 'string', Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale'])],
            'options' => 'nullable|array', // 'nullable' berarti field boleh tidak ada di request
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
