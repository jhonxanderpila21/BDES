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
     * Display a unified listing of assets, categories, and types.
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'assets');
        if (!in_array($activeTab, ['assets', 'categories', 'types'], true)) {
            $activeTab = 'assets';
        }

        // High-level KPI metrics
        $metrics = [
            'total_assets'     => Asset::count(),
            'available_assets' => Asset::where('status', 'available')->count(),
            'total_categories' => AssetCategory::count(),
            'total_types'      => AssetType::count(),
        ];

        // Dropdown reference datasets
        $categories = AssetCategory::orderBy('name')->get();
        $types = AssetType::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        // 1. Assets Inventory Tab Data
        $assetsQuery = Asset::with(['category', 'type', 'location']);
        if ($request->filled('search') && $activeTab === 'assets') {
            $search = $request->search;
            $assetsQuery->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category_id')) {
            $assetsQuery->where('category_id', $request->category_id);
        }
        if ($request->filled('type_id')) {
            $assetsQuery->where('type_id', $request->type_id);
        }
        if ($request->filled('condition')) {
            $assetsQuery->where('condition', $request->condition);
        }
        if ($request->filled('status') && $activeTab === 'assets') {
            $assetsQuery->where('status', $request->status);
        }
        $assets = $assetsQuery->latest('id')->paginate(10, ['*'], 'assets_page')->withQueryString();

        // 2. Asset Categories Tab Data
        $categoriesQuery = AssetCategory::withCount('assets');
        if ($request->filled('search') && $activeTab === 'categories') {
            $search = $request->search;
            $categoriesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status') && $activeTab === 'categories') {
            $categoriesQuery->where('status', $request->status);
        }
        $categoriesList = $categoriesQuery->orderBy('name')->paginate(10, ['*'], 'categories_page')->withQueryString();

        // 3. Asset Types Tab Data
        $typesQuery = AssetType::with('category')->withCount('assets');
        if ($request->filled('search') && $activeTab === 'types') {
            $search = $request->search;
            $typesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($catQ) use ($search) {
                      $catQ->where('name', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('category_id') && $activeTab === 'types') {
            $typesQuery->where('category_id', $request->category_id);
        }
        $typesList = $typesQuery->orderBy('name')->paginate(10, ['*'], 'types_page')->withQueryString();

        return view('admin.assets.index', compact(
            'activeTab',
            'metrics',
            'assets',
            'categories',
            'types',
            'locations',
            'categoriesList',
            'typesList'
        ));
    }

    /**
     * Show the form for creating a new asset.
     */
    public function create()
    {
        return redirect()->route('assets.index', ['tab' => 'assets']);
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'category_id'     => ['required', 'exists:asset_categories,id'],
            'type_id'         => ['nullable', 'exists:asset_types,id'],
            'serial_number'   => ['nullable', 'string', 'max:255'],
            'manufacturer'    => ['nullable', 'string', 'max:255'],
            'purchase_date'   => ['nullable', 'date'],
            'warranty_expiry' => ['nullable', 'date'],
            'location_id'     => ['required', 'exists:locations,id'],
            'condition'       => ['required', 'in:excellent,good,fair,poor'],
            'status'          => ['required', 'in:available,deployed,under_maintenance,out_of_service'],
            'notes'           => ['nullable', 'string'],
        ]);

        do {
            $validated['code'] = 'AST-' . Str::upper(Str::random(8));
        } while (Asset::where('code', $validated['code'])->exists());

        Asset::create($validated);

        return redirect()->route('assets.index', ['tab' => 'assets'])
            ->with('success', 'Asset created successfully!');
    }

    /**
     * Display the specified asset.
     */
    public function show(Asset $asset)
    {
        $asset->load(['category', 'type', 'location', 'maintenance', 'inspections']);

        return view('admin.assets.show', [
            'asset' => $asset,
        ]);
    }

    /**
     * Show the form for editing the specified asset.
     */
    public function edit(Asset $asset)
    {
        return redirect()->route('assets.index', ['tab' => 'assets']);
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'code'            => 'required|unique:assets,code,' . $asset->id,
            'name'            => 'required|string|max:255',
            'category_id'     => 'required|exists:asset_categories,id',
            'type_id'         => 'nullable|exists:asset_types,id',
            'serial_number'   => 'nullable|string|max:255',
            'manufacturer'    => 'nullable|string|max:255',
            'purchase_date'   => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'location_id'     => 'required|exists:locations,id',
            'condition'       => 'required|in:excellent,good,fair,poor',
            'status'          => 'required|in:available,deployed,under_maintenance,out_of_service',
            'notes'           => 'nullable|string',
        ]);

        $asset->update($validated);

        return redirect()->route('assets.index', ['tab' => 'assets'])
            ->with('success', 'Asset updated successfully!');
    }

    /**
     * Remove the specified asset from storage.
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('assets.index', ['tab' => 'assets'])
            ->with('success', 'Asset deleted successfully!');
    }

    /**
     * Search assets
     */
    public function search(Request $request)
    {
        return redirect()->route('assets.index', [
            'tab'    => 'assets',
            'search' => $request->get('q'),
        ]);
    }
}
