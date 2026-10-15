<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of locations.
     */
    public function index()
    {
        $locations = Location::withCount('assets')->paginate(10);
        return view('admin.locations.index', ['locations' => $locations]);
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $input = $request->all();
        if (isset($input['name'])) {
            $input['name'] = strip_tags(trim($input['name']));
        }
        if (isset($input['type'])) {
            $input['type'] = strip_tags(trim($input['type']));
        }
        if (isset($input['address'])) {
            $input['address'] = strip_tags(trim($input['address']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'name'             => 'required|string|max:255|unique:locations,name',
            'type'             => 'nullable|string|max:100',
            'address'          => 'nullable|string|max:255',
            'storage_capacity' => 'nullable|integer|min:0',
        ]);

        Location::create($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Location created successfully!');
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, Location $location)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $input = $request->all();
        if (isset($input['name'])) {
            $input['name'] = strip_tags(trim($input['name']));
        }
        if (isset($input['type'])) {
            $input['type'] = strip_tags(trim($input['type']));
        }
        if (isset($input['address'])) {
            $input['address'] = strip_tags(trim($input['address']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'name'             => 'required|string|max:255|unique:locations,name,' . $location->id,
            'type'             => 'nullable|string|max:100',
            'address'          => 'nullable|string|max:255',
            'storage_capacity' => 'nullable|integer|min:0',
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Location updated successfully!');
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(Request $request, Location $location)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', 'Location deleted successfully!');
    }
}
