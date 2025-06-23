<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Response;
use App\Models\ResponseAnswer;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\FormResponseResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ResponsesImport;

class ResponseController extends Controller
{
    public function index(Request $request)
    {
        $forms = Form::all();
        $query = Response::with('form.teacher', 'student')
            ->select('form_id', DB::raw('count(*) as total_responses'))
            ->groupBy('form_id');

        if ($request->filled('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        $responsesSummary = $query->get();
        return view('responses.index', compact('responsesSummary', 'forms'));
    }

    public function showResponsesByForm(Form $form)
    {
        $questions = $form->questions()->orderBy('id')->get();
        $responses = $form->responses()->with('student')->latest()->get();

        return view('responses.detail_by_form', compact('form', 'questions', 'responses'));
    }

    public function showResponseDetail(Response $response)
    {
        $response->load(['student', 'form.teacher', 'responseAnswers.question']);
        return view('responses.show', compact('response'));
    }

    public function edit(Response $response)
    {
        return view('responses.edit', compact('response'));
    }

    public function update(Request $request, Response $response)
    {
        $validated = $request->validate([
            'photo_path' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'formatted_address' => 'nullable|string|max:255',
            'is_location_valid' => 'nullable|boolean',
        ]);

        if ($request->hasFile('photo_path')) {
            if ($response->photo_path && Storage::exists($response->photo_path)) {
                Storage::delete($response->photo_path);
            }
            $validated['photo_path'] = $request->file('photo_path')->store('responses/photos', 'public');
        }

        $validated['is_location_valid'] = $request->has('is_location_valid');
        $response->update($validated);

        return redirect()->route('responses.detail_by_form', $response->form->id)->with('success', 'Respon berhasil diperbarui.');
    }

    public function destroy(Response $response)
    {
        $response->delete();
        return redirect()->route('responses.index')->with('success', 'Response berhasil dihapus.');
    }

    public function showImportFormByForm(Form $form)
    {
        return view('responses.import', compact('form'));
    }

    public function importExcelByForm(Request $request, Form $form)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
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
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    // --- API ---

    public function apiIndex()
    {
        return response()->json(Response::with('responseAnswers')->get());
    }

    public function apiStore(Request $request)
    {
        $user = $request->user();

        if (!$user || !($user instanceof Student)) {
            return response()->json(['message' => 'Akses ditolak: Hanya siswa yang dapat mengirimkan respons.'], 403);
        }

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
        $photoPath = $request->file('photo')->store('response_photos', 'public');

        $formResponse = Response::create([
            'form_id' => $validatedData['form_id'],
            'student_id' => $user->id,
            'photo_path' => $photoPath,
            'latitude' => $validatedData['latitude'] ?? null,
            'longitude' => $validatedData['longitude'] ?? null,
            'is_location_valid' => $request->input('is_location_valid_from_client', true),
            'submitted_at' => now(),
        ]);

        $answersArray = json_decode($validatedData['answers'], true);
        if (is_array($answersArray)) {
            foreach ($answersArray as $answerData) {
                if (isset($answerData['question_id']) && array_key_exists('answer_text', $answerData)) {
                    $questionExists = Question::where('id', $answerData['question_id'])
                        ->where('form_id', $validatedData['form_id'])
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

        $formResponse->load(['student', 'form.teacher', 'responseAnswers.question']);
        return new FormResponseResource($formResponse);
    }

    public function apiIndexByForm(Request $request, Form $form)
    {
        if ($request->user()->id !== $form->teacher_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $responses = Response::where('form_id', $form->id)
            ->with('student')
            ->latest('submitted_at')
            ->get();

        return FormResponseResource::collection($responses);
    }

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

    public function apiDestroy(Response $response)
    {
        $response->delete();
        return response()->json(['message' => 'Response deleted']);
    }
}
