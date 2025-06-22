<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\FavoriteFormController;

Route::get('/admins', [AdminController::class, 'apiIndex']);

// Route::get('/cek-api', function () {
//     return response()->json(['message' => 'API aktif']);
// });
Route::post('/register/teacher', [AuthController::class, 'registerTeacher']);
Route::post('/register/student', [AuthController::class, 'registerStudent']);
Route::post('/login', [AuthController::class, 'login']); // Tambahkan ini jika belum ada

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logoutUser']); // Tambahkan ini
    Route::get('/user', [AuthController::class, 'getAuthenticatedUser']); // Tambahkan ini
	Route::post('/profile', [UserController::class, 'apiUpdateUserProfile']);
    // API untuk siswa
    Route::get('/students', [StudentController::class, 'apiIndex']);
  	Route::get('/student/responses/history', [StudentController::class, 'apiGetResponseHistory']);

    // API untuk guru
    Route::get('/teachers', [TeacherController::class, 'apiIndex']);
 	Route::get('/teacher/forms/history', [TeacherController::class, 'apiGetFormsHistory']);
  
    // API untuk form
    Route::get('/forms', [FormController::class, 'apiIndex']);
    Route::post('/forms', [FormController::class, 'apiStore']);
    Route::put('/forms/{form}', [FormController::class, 'apiUpdate']);
    Route::delete('/forms/{form}', [FormController::class, 'apiDestroy']);
   	Route::get('/forms/{form}', [FormController::class, 'apiShow']);

    // API untuk question
    Route::get('/questions', [QuestionController::class, 'apiIndex']);
    Route::post('/questions', [QuestionController::class, 'apiStore']);
    Route::put('/questions/{question}', [QuestionController::class, 'apiUpdate']);
    Route::delete('/questions/{question}', [QuestionController::class, 'apiDestroy']);

    // API untuk response
    Route::get('/responses', [ResponseController::class, 'apiIndex']);
    Route::post('/responses', [ResponseController::class, 'apiStore']);
    Route::delete('/responses/{response}', [ResponseController::class, 'apiDestroy']);
    Route::get('/responses/{response}', [ResponseController::class, 'apiShowResponseDetail']);
  	Route::get('/forms/{form}/responses', [ResponseController::class, 'apiIndexByForm']);
  
   	//FAVORIT FORMS
    Route::get('/favorites', [FavoriteFormController::class, 'index']);
    Route::post('/forms/{form}/favorite', [FavoriteFormController::class, 'store']);
    Route::delete('/forms/{form}/favorite', [FavoriteFormController::class, 'destroy']);
  	Route::get('/forms/code/{form_code}', [FormController::class, 'apiGetByFormCode']);
  
});
