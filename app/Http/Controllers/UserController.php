<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Student;
use App\Models\Teacher;

class UserController extends Controller
{
    /**
     * Memperbarui profil pengguna yang terautentikasi.
     */
    public function apiUpdateUserProfile(Request $request)
    {
        $user = $request->user();

        // Validasi untuk memastikan data yang masuk sesuai format yang diharapkan
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:4096', // maks 4MB
            'grade' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
        ]);

        // Menggunakan fill untuk memperbarui data dengan lebih efisien
        // hanya atribut yang ada di $fillable model yang akan di-update
        if ($user instanceof Teacher) {
            $user->fill($request->only(['name', 'address', 'subject']));
        } elseif ($user instanceof Student) {
            $user->fill($request->only(['name', 'address', 'grade']));
        }

        // Logika untuk menangani unggahan foto profil
        if ($request->hasFile('profile_photo')) {
            // Hapus foto profil lama jika ada untuk menghemat ruang penyimpanan
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            // Simpan foto yang baru dan perbarui path di database
            $user->profile_photo_path = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        // Simpan semua perubahan ke database
        $user->save();

        // Kembalikan data user yang sudah diperbarui, diformat oleh UserResource
        return new UserResource($user);
    }
}