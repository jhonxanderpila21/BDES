<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    /**
     * Display a listing of asset categories within the unified Assets hub.
     */
    public function index(Request $request)
    {
        return app(AssetController::class)->index($request->merge(['tab' => 'categories']));
    }

    /**
     * Store a newly created asset category in storage.
     */
    public function store(Request $request)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        $input = $request->all();
        if (isset($input['name'])) {
            $input['name'] = strip_tags(trim($input['name']));
        }
        if (isset($input['description'])) {
            $input['description'] = strip_tags(trim($input['description']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:asset_categories,name',
            'description' => 'nullable|string',
            'status'      => 'in:active,inactive',
        ]);

        AssetCategory::create($validated);

        return redirect()->route('assets.index', ['tab' => 'categories'])
            ->with('success', 'Category created successfully!');
    }

    /**
     * Update the specified asset category in storage.
     */
    public function update(Request $request, AssetCategory $assetCategory)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        $input = $request->all();
        if (isset($input['name'])) {
            $input['name'] = strip_tags(trim($input['name']));
        }
        if (isset($input['description'])) {
            $input['description'] = strip_tags(trim($input['description']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:asset_categories,name,' . $assetCategory->id,
            'description' => 'nullable|string',
            'status'      => 'in:active,inactive',
        ]);

        $assetCategory->update($validated);

        return redirect()->route('assets.index', ['tab' => 'categories'])
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified asset category from storage.
     */
    public function destroy(Request $request, AssetCategory $assetCategory)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        $assetCategory->delete();

        return redirect()->route('assets.index', ['tab' => 'categories'])
            ->with('success', 'Category deleted successfully!');
    }
}
