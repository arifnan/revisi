<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\View;        // tambahkan ini
// use Illuminate\Support\Facades\Redirect;    // tambahkan ini
// use Illuminate\Support\Facades\Response;    // tambahkan ini
class AdminController extends Controller
{
    public function index() {
        $admins = Admin::paginate(10); // Misalnya 10 item per halaman
        return view('admin.index', compact('admins'));
    }
    public function apiIndex()
    {
        $admins = Admin::all();
        return response()->json([
            'status' => true,
            'message' => 'Data admin ditemukan',
            'data' => $admins
        ], 200);
    }
    
    public function create() {
        return view('admin.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins|max:255', // diperbaiki ke tabel admins
            'password' => 'required|min:6|confirmed',
        ]);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.index')->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit($id) {
        $admin = Admin::findOrFail($id);
        return view('admin.edit', compact('admin'));
    }

    public function update(Request $request, $id) {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $id, // diperbaiki ke tabel admins
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('admin.index')->with('success', 'Admin berhasil diperbarui.');
    }

    public function destroy($id) {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return redirect()->route('admin.index')->with('success', 'Admin berhasil dihapus.');
    }
}
