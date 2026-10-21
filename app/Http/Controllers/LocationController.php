<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of locations.
     */
    public function index(Request $request)
    {
        $query = Location::withCount('assets');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $locations = $query->orderBy('name')->paginate(10)->withQueryString();
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

        $created = Location::create($validated);

        if ($request->input('return_to') === 'assets') {
            return redirect()->route('assets.index', [
                'tab'                  => 'assets',
                'reopen_add_asset'     => 1,
                'selected_location_id' => $created->id,
            ])->with('success', 'Location created successfully!');
        }

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
