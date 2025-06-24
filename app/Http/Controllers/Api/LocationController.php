<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Pastikan ini ada

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = Location::all();
        return response()->json($locations);
    }

    /**
     * Memvalidasi lokasi pengguna berdasarkan latitude dan longitude.
     */
    public function validateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $userLat = $request->latitude;
        $userLng = $request->longitude;

        // --- PERUBAHAN DI SINI ---
        // Kita akan langsung memasukkan variabel ke dalam string query
        // karena sudah divalidasi sebagai numerik.
        $locations = Location::select(
            'id',
            'name',
            'latitude',
            'longitude',
            'radius',
            DB::raw("
                ( 6371 * acos( cos( radians($userLat) ) *
                cos( radians( latitude ) )
                * cos( radians( longitude ) - radians($userLng)
                ) + sin( radians($userLat) ) *
                sin( radians( latitude ) ) )
                ) AS distance
            ") // <-- Array binding dihilangkan
        )
        ->get();
        // --- AKHIR PERUBAHAN ---
        
        $validLocation = null;
        foreach ($locations as $location) {
            // Radius dari DB (meter) dibagi 1000 untuk menjadi KM
            if ($location->distance <= ($location->radius / 1000)) {
                $validLocation = $location;
                break; // Hentikan loop jika sudah ketemu lokasi yang valid
            }
        }

        if ($validLocation) {
            return response()->json([
                'status' => 'valid',
                'message' => 'Lokasi Anda valid.',
                'location_name' => $validLocation->name
            ], 200);
        }

        return response()->json([
            'status' => 'invalid',
            'message' => 'Anda berada di luar lokasi yang diperbolehkan.'
        ], 403);
    }
}