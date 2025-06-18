<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
// use Illuminate\Support\Facades\View;        // tambahkan ini
// use Illuminate\Support\Facades\Redirect;    // tambahkan ini
// use Illuminate\Support\Facades\Response;    // tambahkan ini

class AuthController extends Controller
{
    public function showRegister() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users', // Perhatikan: unique:users mungkin seharusnya unique:admins jika tabel Anda bernama admins
            'password' => 'required|min:6|confirmed',
        ]);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'role' => 'admin' // Kolom 'role' tidak ada di $fillable model Admin.php, tambahkan jika memang ada kolomnya di database
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function showLogin() {
        return view('auth.login');
    }

    public function login(Request $request) {
        // 1. Dump semua data request yang masuk
        //dd($request->all()); 

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // 2. Siapkan kredensial untuk upaya login
        $credentials = $request->only('email', 'password');
        
        // Dump kredensial sebelum mencoba login
         //dd($credentials);

        // 3. Coba lakukan autentikasi menggunakan guard 'admin' (sesuai konfigurasi di auth.php)
        // Auth::attempt() akan secara otomatis menggunakan default guard ('admin')
        // dan provider ('admins') yang merujuk ke model App\Models\Admin
        $authAttempt = Auth::attempt($credentials);

        // Dump hasil dari Auth::attempt()
        // Hasilnya akan boolean (true jika berhasil, false jika gagal)
        //dd($authAttempt); 

        if ($authAttempt) {
            // Jika autentikasi berhasil, dump informasi user yang berhasil login
            //dd(Auth::user()); 

            // Regenerate session ID untuk keamanan
            $request->session()->regenerate();
            
            return redirect()->intended('dashboard'); // Gunakan intended() untuk redirect ke halaman yang dituju sebelum login, atau dashboard jika tidak ada
        }
    
        // Jika autentikasi gagal
        //dd('Login Gagal'); 

        return back()->with('error', 'Email atau password salah.')->withInput($request->only('email'));
    }
    

    public function logout(Request $request) { // Tambahkan Request $request
        Auth::logout();
        
        $request->session()->invalidate(); // Membatalkan sesi saat ini
    
        $request->session()->regenerateToken(); // Membuat token CSRF baru

        return redirect()->route('login');
    }
}