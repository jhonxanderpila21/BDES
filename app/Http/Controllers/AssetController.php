<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    /**
     * Display a listing of assets.
     */
    public function index(Request $request)
    {
        $assetsQuery = Asset::with(['category', 'type', 'location']);

        if ($request->filled('status')) {
            $assetsQuery->where('status', $request->input('status'));
        }

        $assets = $assetsQuery->paginate(10)->withQueryString();

        $categories = AssetCategory::where('status', 'active')
            ->orderBy('name')
            ->get();
        $types = AssetType::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('admin.assets.index', [
            'assets' => $assets,
            'categories' => $categories,
            'types' => $types,
            'locations' => $locations,
        ]);
    }

    /**
     * Show the form for creating a new asset.
     */
    public function create()
    {
        $categories = AssetCategory::where('status', 'active')->get();
        $types = AssetType::all();
        $locations = Location::all();

        return view('admin.assets.create', [
            'categories' => $categories,
            'types' => $types,
            'locations' => $locations,
        ]);
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:asset_categories,id'],
            'serial_number' => ['required', 'string', 'max:255'],
            'location_id' => ['required', 'exists:locations,id'],
            'condition' => ['required', 'in:excellent,good,fair,poor'],
            'status' => ['required', 'in:available,deployed,under_maintenance,out_of_service'],
        ]);

        do {
            $validated['code'] = 'AST-'.Str::upper(Str::random(8));
        } while (Asset::where('code', $validated['code'])->exists());

        Asset::create($validated);

        return redirect()->route('assets.index')
            ->with('success', 'Asset created successfully!');
    }

    /**
     * Display the specified asset.
     */
    public function show(Asset $asset)
    {
        $asset->load(['category', 'type', 'location']);

        return view('admin.assets.show', [
            'asset' => $asset,
        ]);
    }

    /**
     * Show the form for editing the specified asset.
     */
    public function edit(Asset $asset)
    {
        $categories = AssetCategory::where('status', 'active')->get();
        $types = AssetType::all();
        $locations = Location::all();

        return view('admin.assets.edit', [
            'asset' => $asset,
            'categories' => $categories,
            'types' => $types,
            'locations' => $locations,
        ]);
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'code' => 'required|unique:assets,code,' . $asset->id,
            'name' => 'required|string',
            'category_id' => 'nullable|exists:asset_categories,id',
            'type_id' => 'nullable|exists:asset_types,id',
            'serial_number' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'location_id' => 'nullable|exists:locations,id',
            'condition' => 'in:excellent,good,fair,poor',
            'status' => 'in:available,deployed,under_maintenance,out_of_service',
            'notes' => 'nullable|string',
        ]);

        $asset->update($validated);

        return redirect()->route('assets.index')
            ->with('success', 'Asset updated successfully!');
    }

    /**
     * Remove the specified asset from storage.
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', 'Asset deleted successfully!');
    }

    /**
     * Search assets
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $assets = Asset::with(['category', 'type', 'location'])
            ->where('code', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->orWhere('serial_number', 'like', "%{$query}%")
            ->paginate(10);

        $categories = AssetCategory::where('status', 'active')
            ->orderBy('name')
            ->get();
        $types = AssetType::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('admin.assets.index', [
            'assets' => $assets,
            'categories' => $categories,
            'types' => $types,
            'locations' => $locations,
        ]);
    }
}
