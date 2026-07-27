<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    /**
     * Display a listing of asset types.
     */
    public function index()
    {
        $types = AssetType::with('category')->paginate(10);
        $categories = AssetCategory::orderBy('name')->get();

        return view('admin.asset-types.index', [
            'types' => $types,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created asset type in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:asset_categories,id',
            'name' => 'required|string',
            'serialized' => 'boolean',
            'borrowable' => 'boolean',
            'requires_maintenance' => 'boolean',
            'requires_inspection' => 'boolean',
            'has_accessories' => 'boolean',
            'has_attachments' => 'boolean',
            'requires_operational_requirements' => 'boolean',
        ]);

        AssetType::create($validated);

        return redirect()->route('asset-types.index')
            ->with('success', 'Asset type created successfully!');
    }

    /**
     * Update the specified asset type in storage.
     */
    public function update(Request $request, AssetType $assetType)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:asset_categories,id',
            'name' => 'required|string',
            'serialized' => 'boolean',
            'borrowable' => 'boolean',
            'requires_maintenance' => 'boolean',
            'requires_inspection' => 'boolean',
            'has_accessories' => 'boolean',
            'has_attachments' => 'boolean',
            'requires_operational_requirements' => 'boolean',
        ]);

        $assetType->update($validated);

        return redirect()->route('asset-types.index')
            ->with('success', 'Asset type updated successfully!');
    }

    /**
     * Remove the specified asset type from storage.
     */
    public function destroy(AssetType $assetType)
    {
        $assetType->delete();

        return redirect()->route('asset-types.index')
            ->with('success', 'Asset type deleted successfully!');
    }
}
