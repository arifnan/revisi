<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // --- STUDENTS TABLE ---
        // Hapus unique index pada email terlebih dahulu (jika ada)
        $indexNameEmailStudents = 'students_email_unique'; 
        if ($this->hasIndex('students', $indexNameEmailStudents)) {
            DB::statement("ALTER TABLE `students` DROP INDEX `{$indexNameEmailStudents}`");
        }
        if ($this->hasIndex('students', 'students_email_unique_text_idx')) {
             DB::statement("ALTER TABLE `students` DROP INDEX `students_email_unique_text_idx`");
        }

        // Ubah tipe kolom menggunakan raw SQL
        // Kolom yang dienkripsi akan menjadi TEXT
        DB::statement("ALTER TABLE `students` MODIFY `name` TEXT NOT NULL");
        DB::statement("ALTER TABLE `students` MODIFY `address` TEXT NULL"); 
        DB::statement("ALTER TABLE `students` MODIFY `profile_photo_path` TEXT NULL");
        DB::statement("ALTER TABLE `students` MODIFY `grade` TEXT NOT NULL"); // Grade dienkripsi, jadi TEXT

        // Kolom yang tidak dienkripsi tetap VARCHAR(255)
        DB::statement("ALTER TABLE `students` MODIFY `email` VARCHAR(255) NOT NULL"); 

        // Tambahkan kembali unique index pada email (VARCHAR)
        DB::statement("ALTER TABLE `students` ADD UNIQUE `students_email_unique` (`email`)");


        // --- TEACHERS TABLE ---
        // Hapus unique index pada nip dan email terlebih dahulu (jika ada)
        $indexNameNipTeachers = 'teachers_nip_unique';
        $indexNameEmailTeachers = 'teachers_email_unique';
        if ($this->hasIndex('teachers', $indexNameNipTeachers)) {
            DB::statement("ALTER TABLE `teachers` DROP INDEX `{$indexNameNipTeachers}`");
        }
        if ($this->hasIndex('teachers', $indexNameEmailTeachers)) {
            DB::statement("ALTER TABLE `teachers` DROP INDEX `{$indexNameEmailTeachers}`");
        }
        // Periksa juga custom index yang mungkin dibuat sebelumnya
        if ($this->hasIndex('teachers', 'teachers_nip_unique_text_idx')) {
             DB::statement("ALTER TABLE `teachers` DROP INDEX `teachers_nip_unique_text_idx`");
        }
        if ($this->hasIndex('teachers', 'teachers_email_unique_text_idx')) {
             DB::statement("ALTER TABLE `teachers` DROP INDEX `teachers_email_unique_text_idx`");
        }

        // Ubah tipe kolom menggunakan raw SQL
        // Kolom yang dienkripsi akan menjadi TEXT
        DB::statement("ALTER TABLE `teachers` MODIFY `name` TEXT NOT NULL");
        DB::statement("ALTER TABLE `teachers` MODIFY `address` TEXT NULL");
        DB::statement("ALTER TABLE `teachers` MODIFY `subject` TEXT NOT NULL");
        DB::statement("ALTER TABLE `teachers` MODIFY `profile_photo_path` TEXT NULL");

        // Kolom yang tidak dienkripsi tetap VARCHAR(255)
        DB::statement("ALTER TABLE `teachers` MODIFY `nip` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `teachers` MODIFY `email` VARCHAR(255) NOT NULL");
        
        // Tambahkan kembali unique index pada nip dan email (VARCHAR)
        DB::statement("ALTER TABLE `teachers` ADD UNIQUE `teachers_nip_unique` (`nip`)");
        DB::statement("ALTER TABLE `teachers` ADD UNIQUE `teachers_email_unique` (`email`)");


        // --- RESPONSES TABLE ---
        // Kolom yang dienkripsi akan menjadi TEXT
        DB::statement("ALTER TABLE `responses` MODIFY `photo_path` TEXT NULL");
        DB::statement("ALTER TABLE `responses` MODIFY `latitude` TEXT NULL");
        DB::statement("ALTER TABLE `responses` MODIFY `longitude` TEXT NULL");


        // --- RESPONSE_ANSWERS TABLE ---
        // Kolom yang dienkripsi akan menjadi LONGTEXT (untuk answer_text)
        DB::statement("ALTER TABLE `response_answers` MODIFY `answer_text` LONGTEXT NULL"); 

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Operasi pembalikan dengan raw SQL.
        // Penting: Kolom yang dienkripsi harus dikembalikan ke TEXT/LONGTEXT, bukan VARCHAR(255),
        // untuk menghindari pemotongan data terenkripsi.

        // STUDENTS TABLE
        if ($this->hasIndex('students', 'students_email_unique')) { 
            DB::statement("ALTER TABLE `students` DROP INDEX `students_email_unique`");
        }
        // Kolom yang dienkripsi dikembalikan ke TEXT (atau tipe aslinya jika bukan VARCHAR)
        DB::statement("ALTER TABLE `students` MODIFY `name` TEXT NOT NULL"); // Tetap TEXT
        DB::statement("ALTER TABLE `students` MODIFY `address` TEXT NULL"); // Tetap TEXT
        DB::statement("ALTER TABLE `students` MODIFY `grade` TEXT NOT NULL"); // Tetap TEXT
        DB::statement("ALTER TABLE `students` MODIFY `profile_photo_path` TEXT NULL"); // Tetap TEXT

        // Kolom yang tidak dienkripsi dikembalikan ke VARCHAR(255)
        DB::statement("ALTER TABLE `students` MODIFY `email` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `students` ADD UNIQUE `students_email_unique` (`email`)"); // Tambahkan kembali


        // TEACHERS TABLE
        if ($this->hasIndex('teachers', 'teachers_nip_unique')) {
            DB::statement("ALTER TABLE `teachers` DROP INDEX `teachers_nip_unique`");
        }
        if ($this->hasIndex('teachers', 'teachers_email_unique')) {
            DB::statement("ALTER TABLE `teachers` DROP INDEX `teachers_email_unique`");
        }
        // Kolom yang dienkripsi dikembalikan ke TEXT (atau tipe aslinya jika bukan VARCHAR)
        DB::statement("ALTER TABLE `teachers` MODIFY `name` TEXT NOT NULL"); // Tetap TEXT
        DB::statement("ALTER TABLE `teachers` MODIFY `address` TEXT NULL"); // Tetap TEXT
        DB::statement("ALTER TABLE `teachers` MODIFY `subject` TEXT NOT NULL"); // Tetap TEXT
        DB::statement("ALTER TABLE `teachers` MODIFY `profile_photo_path` TEXT NULL"); // Tetap TEXT

        // Kolom yang tidak dienkripsi dikembalikan ke VARCHAR(255)
        DB::statement("ALTER TABLE `teachers` MODIFY `nip` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `teachers` ADD UNIQUE `teachers_nip_unique` (`nip`)");
        DB::statement("ALTER TABLE `teachers` MODIFY `email` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `teachers` ADD UNIQUE `teachers_email_unique` (`email`)");


        // RESPONSES TABLE
        // Kolom yang dienkripsi dikembalikan ke TEXT
        DB::statement("ALTER TABLE `responses` MODIFY `photo_path` TEXT NULL"); // Tetap TEXT
        DB::statement("ALTER TABLE `responses` MODIFY `latitude` TEXT NULL"); // Tetap TEXT
        DB::statement("ALTER TABLE `responses` MODIFY `longitude` TEXT NULL"); // Tetap TEXT


        // RESPONSE_ANSWERS TABLE
        // Kolom yang dienkripsi dikembalikan ke LONGTEXT
        DB::statement("ALTER TABLE `response_answers` MODIFY `answer_text` LONGTEXT NULL"); // Tetap LONGTEXT
        
        // Catatan: Kolom lain seperti `gender`, `password`, `created_at`, `updated_at`, `form_id`, `student_id`, `is_location_valid`,
        // `submitted_at`, `question_id`, `option_id`, `file_url`, `formatted_address`
        // seharusnya tidak perlu diubah di metode up()/down() migrasi ini,
        // kecuali ada migrasi lain yang mengubahnya ke TEXT/LONGTEXT.
        // Jika mereka awalnya VARCHAR atau tipe lain yang sesuai, mereka akan tetap seperti itu.
    }

    /**
     * Helper to check if an index exists on a given table.
     * This uses a raw query to avoid Doctrine DBAL issues.
     */
    protected function hasIndex(string $tableName, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            try {
                $indexes = $connection->select("SHOW INDEX FROM `{$tableName}` WHERE Key_name = ?", [$indexName]);
                return !empty($indexes);
            } catch (\Illuminate\Database\QueryException $e) {
                return false; 
            }
        }
        return false;
    }
};
