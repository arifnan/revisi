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

// Route utama yang langsung menampilkan halaman login
Route::get('/', [AuthController::class, 'showLogin'])->name('home');

// **AUTH ROUTES**
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/admins', [AdminController::class, 'index']); // Menampilkan daftar admin




    // **Dashboard**
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // **CRUD Admin**
    Route::resource('admin', AdminController::class)->except(['show']);

    // **CRUD Formulir**
    Route::resource('forms', FormController::class);

    // **CRUD Pertanyaan dalam Formulir**
    Route::resource('questions', QuestionController::class);

    // **Lihat Jawaban User**
    Route::resource('responses', ResponseController::class);
    Route::get('/responses/form/{form}', [ResponseController::class, 'showResponsesByForm'])->name('responses.detail_by_form');
    Route::get('/responses/{response}', [ResponseController::class, 'showResponseDetail'])->name('responses.show');
    Route::get('/responses/create/{form}', [ResponseController::class, 'createResponse'])->name('responses.create');
    Route::post('/responses/store', [ResponseController::class, 'storeResponse'])->name('responses.store');

    // CRUD Guru
    Route::resource('teachers', TeacherController::class);

    // Tambahkan dua rute ini untuk import siswa DI ATAS Route::resource('students', ...)
    Route::get('students/import', [StudentController::class, 'showImportForm'])->name('students.import.form');
    Route::post('students/import', [StudentController::class, 'importExcel'])->name('students.import.excel');
    
    // CRUD Siswa - Pastikan ini setelah rute import kustom
    Route::resource('students', StudentController::class);

    // Export Responses
    Route::get('/export-responses/pdf', [ResponseExportController::class, 'exportPdf'])->name('responses.export.pdf');
    Route::get('/export-responses/excel', [ResponseExportController::class, 'exportExcel'])->name('responses.export.excel');
    
    // Rute import respons sebelumnya (global)
    // Route::get('responses/import', [ResponseController::class, 'showImportForm'])->name('responses.import.form');
    // Route::post('responses/import', [ResponseController::class, 'importExcel'])->name('responses.import.excel');

    // RUTE BARU UNTUK IMPORT RESPON BERDASARKAN FORM ID
    Route::get('forms/{form}/responses/import', [ResponseController::class, 'showImportFormByForm'])->name('responses.import.form.by_form');
    Route::post('forms/{form}/responses/import', [ResponseController::class, 'importExcelByForm'])->name('responses.import.excel.by_form');


    
// **PROTECTED ROUTES (Hanya bisa diakses jika sudah login)**
Route::middleware(['auth:admin'])->group(function () {

});