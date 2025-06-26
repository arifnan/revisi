<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Form;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan ringkasan data.
     */
    public function index()
    {
        // Menghitung jumlah total dari masing-masing model
        $adminCount = Admin::count();
        $teacherCount = Teacher::count();
        $studentCount = Student::count();
        $formCount = Form::count();

        // Mengirimkan data ke view 'dashboard'
        return view('dashboard', compact(
            'adminCount', 
            'teacherCount', 
            'studentCount', 
            'formCount'
        ));
    }
}