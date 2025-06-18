<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB; // Menggunakan DB Facade untuk operasi raw
use Illuminate\Support\Facades\Crypt; // Untuk enkripsi/dekripsi
use Illuminate\Support\Facades\Log; // Untuk logging

class EncryptExistingData extends Command
{
    protected $signature = 'data:encrypt-existing';
    protected $description = 'Encrypts existing data fields using the current APP_KEY directly in DB.';

    public function handle()
    {
        $this->info('Starting data encryption process...');


        // --- START DEBUGGING OUTPUT ---
        $currentAppKey = config('app.key');
        $currentCipher = config('app.cipher');
        $keyLength = mb_strlen($currentAppKey, '8bit'); // Dapatkan panjang byte aktual

        $this->warn("\n--- DEBUGGING ENCRYPTION CONFIG ---");
        $this->warn("APP_KEY (decoded) being used: " . (is_string($currentAppKey) ? $currentAppKey : 'Not a string or empty'));
        $this->warn("APP_KEY Length (bytes): " . $keyLength);
        $this->warn("Cipher being used: " . $currentCipher);
        $this->warn("Expected Cipher: AES-128-CBC");
        $this->warn("Expected Key Length for AES-128-CBC: 16 bytes");
        $this->warn("--- END DEBUGGING ENCRYPTION CONFIG ---\n");
        // --- END DEBUGGING OUTPUT ---


        $this->processTable('students', [
            'name',
            // 'email', // Email tidak dienkripsi
            'address',
            'grade', // Grade kembali dienkripsi
            'profile_photo_path'
        ]);

        $this->processTable('teachers', [
            // 'nip',   // NIP tidak dienkripsi
            'name',
            // 'email', // Email tidak dienkripsi
            'address',
            'subject',
            'profile_photo_path'
        ]);

        $this->processTable('responses', [
            'latitude',
            'longitude',
            'photo_path'
        ]);

        $this->processTable('response_answers', [
            'answer_text'
        ]);

        $this->info('All specified data encryption process finished.');
    }

    /**
     * Generic method to process and encrypt fields for a given table.
     */
    protected function processTable(string $tableName, array $fieldsToEncrypt): void
    {
        $this->info("Encrypting {$tableName} data...");

        $rawRecords = DB::table($tableName)->get(); // Ambil semua data mentah
        $bar = $this->output->createProgressBar(count($rawRecords));
        $bar->start();

        foreach ($rawRecords as $rawRecord) {
            $updateData = [];
            $recordChanged = false;

            foreach ($fieldsToEncrypt as $field) {
                // Dapatkan nilai asli dari objek raw data
                $originalValue = property_exists($rawRecord, $field) ? $rawRecord->{$field} : null;

                // Lewati jika nilai null atau string kosong
                if (is_null($originalValue) || trim((string)$originalValue) === '') {
                    // Jika nilai di DB adalah string kosong atau spasi, pastikan update menjadi NULL
                    // Laravel's encrypted cast otomatis menyimpan null sebagai null
                    if ($originalValue !== null && trim((string)$originalValue) === '') {
                         $updateData[$field] = null;
                         $recordChanged = true;
                    }
                    continue;
                }

                // Coba deteksi apakah sudah terenkripsi oleh Laravel.
                // Payload terenkripsi Laravel adalah JSON string dengan kunci 'iv', 'value', 'mac'.
                $isAlreadyEncryptedPayload = false;
                try {
                    $decoded = json_decode((string)$originalValue, true);
                    if (json_last_error() === JSON_ERROR_NONE &&
                        is_array($decoded) &&
                        isset($decoded['iv']) &&
                        isset($decoded['value']) &&
                        isset($decoded['mac'])) {
                        $isAlreadyEncryptedPayload = true;
                    }
                } catch (\Throwable $e) {
                    // Bukan JSON valid atau masalah lain, anggap belum terenkripsi
                    Log::debug("JSON decode failed for {$tableName}.{$field} (ID: {$rawRecord->id}): " . $e->getMessage());
                }

                if ($isAlreadyEncryptedPayload) {
                    // Ini sudah terlihat seperti payload terenkripsi Laravel.
                    // Asumsi sudah terenkripsi dengan benar (atau dengan kunci lama)
                    // Kita tidak akan mengubahnya untuk menghindari korupsi data.
                    // Tujuan command ini adalah mengenkripsi data yang belum terenkripsi.
                    continue; // Lewati field ini
                }

                // Jika sampai sini, berarti data adalah plaintext atau bukan payload terenkripsi valid.
                // Lakukan enkripsi.
                try {
                    $encryptedValue = Crypt::encryptString((string)$originalValue);
                    $updateData[$field] = $encryptedValue;
                    $recordChanged = true;
                } catch (\Throwable $e) {
                    $this->error("Failed to encrypt {$tableName}.{$field} (ID: {$rawRecord->id}): " . $e->getMessage());
                    // Lanjutkan ke field berikutnya atau record berikutnya, tapi log error
                }
            }

            // Jika ada data yang perlu diupdate, lakukan operasi update.
            if ($recordChanged && !empty($updateData)) {
                DB::table($tableName)
                    ->where('id', $rawRecord->id)
                    ->update($updateData);
                // $this->comment("Updated {$tableName} ID: {$rawRecord->id}"); // Uncomment for more detailed output
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info("\n{$tableName} data encryption complete.");
    }
}
