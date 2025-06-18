<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use App\Http\Resources\FormResource;

class FavoriteFormController extends Controller
{
    /**
     * Menampilkan semua formulir yang difavoritkan oleh user yang login.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Mengambil forms melalui relasi 'favorites' yang akan kita buat di model User/Teacher
        $favoriteForms = $user->favorites()->with(['questions.options', 'teacher'])->get();

        return FormResource::collection($favoriteForms);
    }

    /**
     * Menambahkan sebuah form ke daftar favorit user.
     */
    public function store(Request $request, Form $form)
    {
        $user = $request->user();

        // attach() akan menambahkan record ke tabel pivot tanpa membuat duplikat
        $user->favorites()->attach($form->id);

        return response()->json(['message' => 'Form berhasil ditambahkan ke favorit.'], 200);
    }

    /**
     * Menghapus sebuah form dari daftar favorit user.
     */
    public function destroy(Request $request, Form $form)
    {
        $user = $request->user();

        // detach() akan menghapus record dari tabel pivot
        $user->favorites()->detach($form->id);

        return response()->json(['message' => 'Form berhasil dihapus dari favorit.'], 200);
    }
}