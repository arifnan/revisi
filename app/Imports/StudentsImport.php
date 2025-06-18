<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
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
        // --- Penanganan Kolom 'jenis_kelamin' ---
        $genderValue = null;
        
        if (isset($row['jenis_kelamin'])) {
            $rawGender = $row['jenis_kelamin'];
        } elseif (isset($row['jenis kelamin'])) { // Fallback
            $rawGender = $row['jenis kelamin'];
        } else {
            $rawGender = null;
        }

        if (!is_null($rawGender)) {
            $lowerGender = strtolower(trim($rawGender));
            if ($lowerGender === 'laki-laki' || $lowerGender === 'laki laki' || (is_numeric($rawGender) && (int)$rawGender === 1)) {
                $genderValue = 1;
            } elseif ($lowerGender === 'perempuan' || (is_numeric($rawGender) && (int)$rawGender === 0)) {
                $genderValue = 0;
            } else {
                $genderValue = null;
            }
        }

        // --- Penanganan Kolom 'kelas' ---
        // Tidak perlu konversi eksplisit di sini karena validasi di rules akan menerima angka
        $kelasValue = $row['kelas'] ?? null;


        return new Student([
            'name'     => $row['nama'],
            'gender'    => $genderValue,
            'email'    => $row['email'],
            'password' => Hash::make($row['password']),
            'grade'    => $kelasValue,
            'address'  => $row['address'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required', // Atau 'required|in:0,1'
            'email' => 'required|email|unique:students,email',
            'password' => 'required|min:6',
            'kelas' => 'required|integer', // <<< UBAH KE 'integer' atau 'numeric' jika di DB INT/TINYINT
            'address' => 'nullable|string',
        ];
    }
}