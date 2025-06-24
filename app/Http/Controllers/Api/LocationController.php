<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mengambil semua data dari tabel locations
        $locations = Location::all();
        // Mengembalikan data sebagai respons JSON
        return response()->json($locations);
    }
}