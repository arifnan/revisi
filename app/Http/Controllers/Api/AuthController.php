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
            'user' => new UserResource($teacher->fresh()),
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
            'user' => new UserResource($student->fresh()),
            'token' => $token,
            'role' => 'student'
        ], 201);
    }

    /**
     * Login untuk Guru atau Siswa via API.
     * Method loginUser Anda sudah cukup baik dalam membedakan peran berdasarkan input 'role'
     * dan kemudian mencari di tabel yang sesuai. Tidak perlu diubah untuk masalah ini.
     */
    public function loginUser(Request $request)
    {
        // Versi yang sudah Anda berikan sebelumnya sudah memvalidasi role,
        // dan akan gagal jika role yang di-login tidak cocok dengan kredensial di tabel yang sesuai.
        // Pesan "Akun ini bukan akun siswa. Silakan login sebagai guru." berasal dari logic di Android
        // setelah API mengembalikan data user yang ternyata role-nya tidak sesuai dengan layar login saat ini.
        // Dengan validasi email unik lintas tabel saat registrasi, kasus ini seharusnya tidak terjadi lagi.

        $request->validate([
            'email' => 'required|string', // Di Android, ini dikirim sebagai NIP atau Email untuk Guru
            'password' => 'required|string',
            'role' => ['required', 'string', Rule::in(['teacher', 'student'])]
        ]);

        $credentials = $request->only('email', 'password');
        $role = $request->input('role');
        $user = null;
        $guard = ''; // Tidak terpakai di sini, tapi ok

        if ($role === 'teacher') {
            // Untuk guru, 'email' dari request bisa jadi NIP atau Email
            $user = Teacher::where('email', $credentials['email'])
                            ->orWhere('nip', $credentials['email']) // Tambahkan pengecekan NIP
                            ->first();
        } elseif ($role === 'student') {
            $user = Student::where('email', $credentials['email'])->first();
        }

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'], // Pesan error generik lebih baik
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('api_token_' . ($user instanceof Teacher ? 'guru' : 'siswa') , [$role])->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => new UserResource($user->fresh()->load('notifications', 'favorites')),
            'token' => $token,
            'role' => $role // Kirim role yang digunakan untuk login
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
            'user' => new UserResource($user->fresh()->load('notifications', 'favoriteForms'))
        ]);
    }
}