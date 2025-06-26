<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Form;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\FormResponseResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;


class StudentController extends Controller
{
      public function index(Request $request) {
        $allStudents = Student::all(); // Ambil semua siswa untuk difilter di PHP

        // Filter di PHP setelah semua data diambil dan didekripsi
        if ($request->has('gender') && $request->gender !== '') {
            $genderToFilter = (int)$request->gender;
            $allStudents = $allStudents->where('gender', $genderToFilter);
        }

        if ($request->has('grade') && $request->grade !== '') {
            $gradeToFilter = (string)$request->grade;
            $allStudents = $allStudents->where('grade', $gradeToFilter);
        }

        // Sekarang, kita akan melakukan paginasi pada koleksi yang sudah difilter
        $perPage = 10; // Jumlah siswa per halaman (ubah dari 100 ke 10 untuk contoh)
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $allStudents->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $students = new \Illuminate\Pagination\LengthAwarePaginator($currentItems, count($allStudents), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('students.index', compact('students'));
    }
    

    public function create() {
        return view('students.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required',
            'gender' => 'required|boolean',
            'email' => 'required|email|unique:students',
            'password' => 'required|min:6',
            'grade' => 'required',
            'address' => 'nullable|string',
        ]);
    
        Student::create([
            'name' => $request->name,
            'gender' => $request->gender,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'grade' => $request->grade,
            'address' => $request->address,
        ]);
    
        return redirect()->route('students.index')->with('success', 'Siswa berhasil ditambahkan.');
    }
    
    //menampilkan data api json
    public function apiIndex()
    {
        $query = Student::all();
        return response()->json([
            'status' => true,
            'message' => 'Data murid ditemukan',
            'data' => $query
        ], 200);
    }
  
    public function favoriteForms()
    {
        // 'user_type' akan dicocokkan dengan nama class ini
        return $this->morphToMany(Form::class, 'user', 'favorite_forms');
    }
  
    public function apiGetResponseHistory(Request $request)
    {
        $user = $request->user(); // Mendapatkan siswa yang terautentikasi

        if (!$user || !$user instanceof Student) {
            return response()->json(['message' => 'Unauthorized or not a student.'], 403);
        }

        // Ambil semua 'responses' milik siswa, beserta relasi 'form'
        // 'form.teacher' juga di-load agar nama guru bisa ditampilkan jika perlu
        $responses = $user->submittedResponses()
                           ->with(['form.teacher'])
                           ->latest() // Urutkan berdasarkan yang terbaru diisi
                           ->get();

        if ($responses->isEmpty()) {
            return response()->json(['message' => 'Anda belum mengisi formulir apapun.', 'data' => []], 200);
        }

        // Gunakan FormResponseResource untuk format data yang konsisten
        return FormResponseResource::collection($responses);
    }

    public function showImportForm()
    {
        return view('students.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            // Kita sudah membahas StudentsImport secara terpisah, jadi pastikan itu sudah final
            Excel::import(new StudentsImport, $request->file('file'));
            return redirect()->route('students.index')->with('success', 'Data siswa berhasil diimpor!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return redirect()->back()->with('error', 'Gagal mengimpor data: ' . implode('<br>', $errors));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }
}