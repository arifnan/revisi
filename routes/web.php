<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\ResponseExportController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\DashboardController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rute-rute ini untuk aplikasi web Anda. Rute ini dimuat oleh
| RouteServiceProvider dan semuanya akan diberi grup middleware "web".
|
*/

// --- RUTE PUBLIK (Bisa diakses tanpa login) ---
Route::get('/', [AuthController::class, 'showLogin'])->name('home');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    // **Dashboard**
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // **CRUD Admin**
    Route::resource('admin', AdminController::class)->except(['show']);

    // **CRUD Formulir**
    Route::resource('forms', FormController::class);

    // **CRUD Pertanyaan dalam Formulir**
    Route::resource('questions', QuestionController::class);

    // **Lihat Jawaban User**
    Route::resource('responses', ResponseController::class);
    Route::get('/responses/form/{form}', [ResponseController::class, 'showResponsesByForm'])->name('responses.detail_by_form');
    // Rute 'responses.show' tidak diperlukan jika detail sudah di halaman lain, atau pastikan methodnya ada.
    // Jika tidak ada method 'showResponseDetail', hapus atau beri komentar baris di bawah ini.
    Route::get('/responses/{response}', [ResponseController::class, 'showResponseDetail'])->name('responses.show');
    Route::get('/responses/create/{form}', [ResponseController::class, 'createResponse'])->name('responses.create');
    Route::post('/responses/store', [ResponseController::class, 'storeResponse'])->name('responses.store');

    // CRUD Guru
    Route::resource('teachers', TeacherController::class);

    // Rute import siswa
    Route::get('students/import', [StudentController::class, 'showImportForm'])->name('students.import.form');
    Route::post('students/import', [StudentController::class, 'importExcel'])->name('students.import.excel');
    
    // CRUD Siswa
    Route::resource('students', StudentController::class);
    
    // **CRUD Lokasi (Perbaikan di sini)**
    // Menambahkan ->except(['show']) untuk menghindari error karena method show() tidak ada
    Route::resource('locations', LocationController::class)->except(['show']);

    // Export & Import Responses
    Route::get('/export-responses/pdf', [ResponseExportController::class, 'exportPdf'])->name('responses.export.pdf');
    Route::get('/export-responses/excel', [ResponseExportController::class, 'exportExcel'])->name('responses.export.excel');
    Route::get('forms/{form}/responses/import', [ResponseController::class, 'showImportFormByForm'])->name('responses.import.form.by_form');
    Route::post('forms/{form}/responses/import', [ResponseController::class, 'importExcelByForm'])->name('responses.import.excel.by_form');


// --- RUTE TERPROTEKSI (Hanya bisa diakses oleh ADMIN yang sudah login) ---
Route::middleware(['auth:admin'])->group(function () {
    

});

