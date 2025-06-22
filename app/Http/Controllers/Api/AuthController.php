<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Registrasi untuk Guru via API.
     */
    public function registerTeacher(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:teachers,nip',
            // Validasi email unik di kedua tabel: teachers dan students
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('teachers', 'email'), // Unik di tabel teachers
                Rule::unique('students', 'email')  // Juga unik di tabel students
            ],
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'required|boolean',
            'subject' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        $teacher = Teacher::create([
            'name' => $validatedData['name'],
            'nip' => $validatedData['nip'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'gender' => $validatedData['gender'],
            'subject' => $validatedData['subject'],
            'address' => $validatedData['address'] ?? null,
        ]);

        $token = $teacher->createToken('api_token_guru')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi guru berhasil',
            'user' => new UserResource($teacher->fresh()->load('notifications', 'favorites')), // Relasi 'favorites'
            'token' => $token,
            'role' => 'teacher'
        ], 201);
    }

    /**
     * Registrasi untuk Siswa via API.
     */
    public function registerStudent(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            // Validasi email unik di kedua tabel: students dan teachers
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('students', 'email'), // Unik di tabel students
                Rule::unique('teachers', 'email')  // Juga unik di tabel teachers
            ],
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'required|boolean',
            'grade' => ['required', 'string', Rule::in(['10', '11', '12'])],
            'address' => 'nullable|string',
        ]);

        $student = Student::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'gender' => $validatedData['gender'],
            'grade' => $validatedData['grade'],
            'address' => $validatedData['address'] ?? null,
        ]);

        $token = $student->createToken('api_token_siswa')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi siswa berhasil',
            'user' => new UserResource($student->fresh()->load('notifications', 'favorites')), // Relasi 'favorites'
            'token' => $token,
            'role' => 'student'
        ], 201);
    }

    /**
     * Login untuk Guru atau Siswa via API.
     */
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            // Field 'role' dihapus dari validasi karena akan dideteksi secara otomatis
        ]);

        $email = $validatedData['email'];
        $password = $validatedData['password'];

        $user = null;
        $guard = null;

        // Coba autentikasi sebagai Siswa
        if (Auth::guard('student')->attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::guard('student')->user();
            $guard = 'student';
        }
        // Jika bukan siswa, coba autentikasi sebagai Guru
        else if (Auth::guard('teacher')->attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::guard('teacher')->user();
            $guard = 'teacher';
        }

        if ($user) {
            // Pastikan Teacher dan Student models menggunakan trait HasApiTokens
            $token = $user->createToken('authToken')->plainTextToken;
            return response()->json([
                'message' => 'Login Berhasil!',
                'token' => $token,
                // Menggunakan 'favorites' yang benar
                'user' => new UserResource($user->load('notifications', 'favorites')) 
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
        ]);
    }


    /**
     * Logout pengguna API yang terautentikasi.
     */
    public function logoutUser(Request $request) //
    {
        $user = $request->user(); 

        if ($user && method_exists($user, 'currentAccessToken')) {
            $token = $user->currentAccessToken();
            if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
                $token->delete();
                return response()->json(['message' => 'Logged out successfully from API']);
            }
        }
        return response()->json(['message' => 'Logout failed or no active API token.'], 400);
    }


    /**
     * Mendapatkan detail pengguna API yang terautentikasi.
     */
    public function getAuthenticatedUser(Request $request) //
    {
        $user = $request->user(); 

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        return response()->json(new UserResource($user->load('notifications', 'favorites')));
    }

    /**
     * Update profil pengguna (Guru atau Siswa) via API.
     */
    public function updateUserProfile(Request $request) //
    {
        $user = $request->user(); 

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        $baseRules = [
            'name' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        $specificRules = [];
        if ($user instanceof Student) {
            $specificRules['grade'] = ['sometimes', 'string', Rule::in(['10', '11', '12'])];
        } elseif ($user instanceof Teacher) {
            $specificRules['subject'] = ['sometimes', 'string', 'max:255'];
             // Guru mungkin juga bisa update NIP atau email, tapi perlu hati-hati dengan unique constraint
             // 'nip' => ['sometimes', 'string', 'max:255', Rule::unique('teachers','nip')->ignore($user->id)],
             // 'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('teachers','email')->ignore($user->id), Rule::unique('students','email')],
        }

        $validatedData = $request->validate(array_merge($baseRules, $specificRules));
        
        $updateData = [];
        if ($request->has('name') && $request->filled('name')) {
            $updateData['name'] = $validatedData['name'];
        }
        if ($request->has('address')) { // Alamat boleh string kosong atau null
            $updateData['address'] = $validatedData['address'];
        }

        if ($user instanceof Student && $request->has('grade') && $request->filled('grade')) {
            $updateData['grade'] = $validatedData['grade'];
        }
        if ($user instanceof Teacher && $request->has('subject') && $request->filled('subject')) {
            $updateData['subject'] = $validatedData['subject'];
        }
        
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada dan path nya tersimpan
            // if ($user->photo_url && Storage::disk('public')->exists($user->photo_url)) {
            //     Storage::disk('public')->delete($user->photo_url);
            // }
            $filePath = $request->file('photo')->store('profile_photos', 'public');
            $updateData['photo_url'] = $filePath; // Pastikan model User/Teacher/Student punya field photo_url & $fillable
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            // Menggunakan 'favorites' yang benar
            'user' => new UserResource($user->fresh()->load('notifications', 'favorites')) 
        ]);
    }
}