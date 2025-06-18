<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Teacher; // <-- Tambahkan ini
use App\Models\Student; // <-- Tambahkan ini

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $roleValue = null;
        $nipValue = null;
        $subjectValue = null;
        $gradeValue = null;
        $addressValue = $this->address; // Alamat umum
        $genderValue = $this->gender;   // Gender umum

        // Tentukan role dan atribut spesifik berdasarkan instance model
        if ($this->resource instanceof Teacher) {
            $roleValue = 'teacher';
            $nipValue = $this->nip;
            $subjectValue = $this->subject;
        } elseif ($this->resource instanceof Student) {
            $roleValue = 'student';
            $gradeValue = $this->grade;
        }
        // Anda bisa menambahkan fallback jika $this->resource adalah instance dari App\Models\User
        // dan memiliki kolom 'role' secara langsung, meskipun dari AuthController Anda
        // sepertinya Anda selalu melewatkan instance Teacher atau Student.
        // else if ($this->resource instanceof \App\Models\User && isset($this->resource->role)) {
        //     $roleValue = $this->resource->role;
        //     if ($roleValue === 'teacher' && isset($this->resource->nip)) {
        //         $nipValue = $this->resource->nip;
        //     }
        // }


        // Logika untuk profile_photo_url
        $profilePhotoUrl = null;
        if (!empty($this->profile_photo_path)) {
            // Jika menggunakan disk publik dan path disimpan relatif terhadap 'storage/app/public'
            $profilePhotoUrl = asset('storage/' . $this->profile_photo_path);
        } elseif (!empty($this->profile_photo_url)) {
            // Jika Anda menyimpan URL lengkap langsung di model (kurang umum untuk path lokal)
            $profilePhotoUrl = $this->profile_photo_url;
        }
        // Jika tidak ada, biarkan null (Android UserApiModel sudah nullable)

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'nip' => $nipValue, // Akan null jika bukan guru
            'role' => $roleValue, // Harus "teacher" atau "student"
            'email_verified_at' => $this->email_verified_at ? $this->email_verified_at->toIso8601String() : null,
            'profile_photo_url' => $profilePhotoUrl,
            'address' => $addressValue,
            'gender' => $genderValue, // Pastikan model Teacher dan Student memiliki atribut 'gender'
            'subject' => $this->when($this->resource instanceof Teacher, $subjectValue), // Hanya ada jika guru
            'grade' => $this->when($this->resource instanceof Student, $gradeValue),     // Hanya ada jika siswa
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,

            // Contoh memuat relasi jika sudah di-load di controller:
            // 'notifications' => NotificationResource::collection($this->whenLoaded('notifications')),
            // 'favorite_forms' => FormResource::collection($this->whenLoaded('favoriteForms')),
            // 'forms_created' => $this->when($this->resource instanceof Teacher, FormResource::collection($this->whenLoaded('forms'))),
            // 'form_responses' => $this->when($this->resource instanceof Student, FormResponseResource::collection($this->whenLoaded('responses'))),
        ];
    }
}
