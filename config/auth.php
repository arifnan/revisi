<?php

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'), // Default untuk sesi web (misalnya, admin panel)
        'passwords' => env('AUTH_PASSWORD_BROKER', 'admins'), // Default untuk password reset
    ],

    'guards' => [
        'web' => [ // Guard untuk autentikasi web (session-based)
            'driver' => 'session',
            'provider' => 'admins', // Jika admin Anda menggunakan model Admin dan provider 'admins'
        ],

        'admin' => [ // Jika Anda memiliki guard admin terpisah untuk web
            'driver' => 'session',
            'provider' => 'admins',
        ],

        // ---- TAMBAHKAN GUARD UNTUK API MENGGUNAKAN SANCTUM (INI YANG PALING PENTING UNTUK API ANDA) ----
        'sanctum' => [
            'driver' => 'sanctum',
            // 'provider' dapat di-set null karena Sanctum akan mencoba mencocokkan token
            // dengan model yang menggunakan HasApiTokens dan terdaftar di 'providers'.
            // Namun, penting bahwa provider untuk Teacher dan Student didefinisikan di bawah.
            'provider' => null,
        ],
        // --------------------------------------------------------------------

        // ---- TAMBAHKAN GUARD UNTUK TEACHER API MENGGUNAKAN SANCTUM ----
        'teacher' => [
            'driver' => 'session',//an driver 'sanctum' untuk API token
            'provider' => 'teachers', // Menunjuk ke provider 'teachers' yang sudah didefinisikan
        ],
        // --------------------------------------------------------------------

        // ---- TAMBAHKAN GUARD UNTUK STUDENT API MENGGUNAKAN SANCTUM ----
        'student' => [
            'driver' => 'session',//an driver 'sanctum' untuk API token
            'provider' => 'students', // Menunjuk ke provider 'students' yang sudah didefinisikan
        ],
        // --------------------------------------------------------------------
    ],

    'providers' => [
        'admins' => [ // Provider untuk model Admin Anda
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],

        // ---- TAMBAHKAN PROVIDER UNTUK MODEL TEACHER (PENTING UNTUK SANCTUM API!) ----
        'teachers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Teacher::class,
        ],
        // ---------------------------------------------------------------------------

        // ---- TAMBAHKAN PROVIDER UNTUK MODEL STUDENT (PENTING UNTUK SANCTUM API!) ----
        'students' => [
            'driver' => 'eloquent',
            'model' => App\Models\Student::class,
        ],
        // ---------------------------------------------------------------------------

        // Provider 'users' default Laravel. Jika model App\Models\User.php Anda
        // adalah model generik yang TIDAK dipakai untuk login Teacher/Student via API,
        // ini mungkin tidak secara langsung dipakai oleh guard 'sanctum' untuk Teacher/Student.
        // 'users' => [
        //     'driver' => 'eloquent',
        //     'model' => App\Models\User::class,
        // ],
    ],

    'passwords' => [ // Konfigurasi untuk reset password
        'admins' => [
            'provider' => 'admins',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        'teachers' => [ // Untuk reset password guru jika ada fitur tersebut
            'provider' => 'teachers',
            'table' => 'password_reset_tokens', // Gunakan tabel yang sama atau buat yang baru
            'expire' => 60,
            'throttle' => 60,
        ],
        'students' => [ // Untuk reset password siswa jika ada fitur tersebut
            'provider' => 'students',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];