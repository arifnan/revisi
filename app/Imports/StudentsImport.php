<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Penting untuk membaca header
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // dd($row); // Anda bisa mengaktifkan baris ini lagi jika error masih berlanjut
                    // dan salin teks outputnya untuk diagnosis yang lebih akurat.

        // --- Penanganan Kolom 'jenis_kelamin' ---
        $genderValue = null;
        
        // Maatwebsite\Excel dengan WithHeadingRow biasanya mengonversi spasi ke underscore.
        // Coba akses dengan 'jenis_kelamin' dulu.
        if (isset($row['jenis_kelamin'])) {
            $rawGender = $row['jenis_kelamin'];
        } elseif (isset($row['jenis kelamin'])) { // Fallback jika header tidak terkonversi otomatis
            $rawGender = $row['jenis kelamin'];
        } else {
            // Jika kolom jenis_kelamin tidak ditemukan sama sekali, ini akan gagal validasi required
            // Atau Anda bisa memberikan nilai default jika itu diperbolehkan di skema DB Anda.
            $rawGender = null;
        }

        if (!is_null($rawGender)) {
            $lowerGender = strtolower(trim($rawGender)); // Trim spasi dan ubah ke huruf kecil
            if ($lowerGender === 'laki-laki' || $lowerGender === 'laki laki' || (is_numeric($rawGender) && (int)$rawGender === 1)) {
                $genderValue = 1; // 1 untuk Laki-laki
            } elseif ($lowerGender === 'perempuan' || (is_numeric($rawGender) && (int)$rawGender === 0)) {
                $genderValue = 0; // 0 untuk Perempuan
            } else {
                // Jika nilai tidak dikenali, set ke null atau default lain.
                // Validasi nanti akan menangkapnya jika required.
                $genderValue = null;
            }
        }


        // --- Penanganan Kolom 'kelas' ---
        // Memastikan 'kelas' selalu dibaca sebagai string, terlepas dari format Excel
        $kelasValue = isset($row['kelas']) ? (string) $row['kelas'] : null;


        // Membuat record Student
        return new Student([
            'name'     => $row['nama'],
            'gender'    => $genderValue, // Gunakan nilai gender yang sudah dikonversi
            'email'    => $row['email'],
            'password' => Hash::make($row['password']),
            'grade'    => $kelasValue, // Gunakan nilai kelas yang sudah dikonversi
            'address'  => $row['address'] ?? null,
        ]);
    }

    public function rules(): array
    {
        // Sesuaikan rules agar sesuai dengan konversi di atas
        return [
            'nama' => 'required|string|max:255',
            // Karena kita melakukan konversi manual gender, validasi cukup 'required' atau 'nullable'
            // dan biarkan logika model() yang menentukan 0/1.
            // Namun, jika Anda ingin validasi yang ketat terhadap 0/1, tambahkan 'boolean' lagi.
            // Untuk saat ini, kita akan biarkan 'required' dan mengandalkan konversi di model().
            'jenis_kelamin' => 'required', // Atau 'required|in:0,1' jika Anda sudah yakin konversi berhasil
            'email' => 'required|email|unique:students,email',
            'password' => 'required|min:6',
            'kelas' => 'required|string|max:255', // Tetap string karena kita memaksa konversi ke string
            'address' => 'nullable|string',
        ];
    }
}