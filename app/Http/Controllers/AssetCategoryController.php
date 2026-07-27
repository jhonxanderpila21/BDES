<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    /**
     * Display a listing of asset categories.
     */
    public function index()
    {
        $categories = AssetCategory::paginate(10);
        return view('admin.asset-categories.index', ['categories' => $categories]);
    }

    /**
     * Store a newly created asset category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:asset_categories,name',
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        AssetCategory::create($validated);

        return redirect()->route('asset-categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Update the specified asset category in storage.
     */
    public function update(Request $request, AssetCategory $assetCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:asset_categories,name,' . $assetCategory->id,
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $assetCategory->update($validated);

        return redirect()->route('asset-categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified asset category from storage.
     */
    public function destroy(AssetCategory $assetCategory)
    {
        $assetCategory->delete();

        return redirect()->route('asset-categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}
