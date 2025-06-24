<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Menampilkan daftar semua lokasi.
     */
    public function index()
    {
        $locations = Location::all();
        return view('locations.index', compact('locations'));
    }

    /**
     * Menampilkan form untuk membuat lokasi baru.
     */
    public function create()
    {
        return view('locations.create');
    }

    /**
     * Menyimpan lokasi baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:0',
        ]);

        Location::create($request->all());

        return redirect()->route('locations.index')
                         ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit lokasi.
     */
    public function edit(Location $location)
    {
        return view('locations.edit', compact('location'));
    }

    /**
     * Memperbarui lokasi di database.
     */
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:0',
        ]);

        $location->update($request->all());

        return redirect()->route('locations.index')
                         ->with('success', 'Lokasi berhasil diperbarui.');
    }

    /**
     * Menghapus lokasi dari database.
     */
    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('locations.index')
                         ->with('success', 'Lokasi berhasil dihapus.');
    }
}