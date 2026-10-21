<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    /**
     * Display a listing of asset types within the unified Assets hub.
     */
    public function index(Request $request)
    {
        return app(AssetController::class)->index($request->merge(['tab' => 'types']));
    }

    /**
     * Store a newly created asset type in storage.
     */
    public function store(Request $request)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->has('name')) {
            $request->merge(['name' => strip_tags(trim($request->name))]);
        }

        $validated = $request->validate([
            'category_id'                       => 'nullable|exists:asset_categories,id',
            'name'                              => 'required|string|max:255',
            'serialized'                        => 'boolean',
            'borrowable'                        => 'boolean',
            'requires_maintenance'              => 'boolean',
            'requires_inspection'               => 'boolean',
            'has_accessories'                   => 'boolean',
            'has_attachments'                   => 'boolean',
            'requires_operational_requirements' => 'boolean',
        ]);

        // Cast checkboxes boolean
        $checkboxes = [
            'serialized', 'borrowable', 'requires_maintenance', 
            'requires_inspection', 'has_accessories', 'has_attachments', 
            'requires_operational_requirements'
        ];
        foreach ($checkboxes as $cb) {
            $validated[$cb] = $request->boolean($cb);
        }

        $created = AssetType::create($validated);

        if ($request->input('return_to') === 'assets') {
            return redirect()->route('assets.index', [
                'tab'              => 'assets',
                'reopen_add_asset' => 1,
                'selected_type_id' => $created->id,
            ])->with('success', 'Asset type created successfully!');
        }

        return redirect()->route('assets.index', ['tab' => 'types'])
            ->with('success', 'Asset type created successfully!');
    }

    /**
     * Update the specified asset type in storage.
     */
    public function update(Request $request, AssetType $assetType)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->has('name')) {
            $request->merge(['name' => strip_tags(trim($request->name))]);
        }

        $validated = $request->validate([
            'category_id'                       => 'nullable|exists:asset_categories,id',
            'name'                              => 'required|string|max:255',
            'serialized'                        => 'boolean',
            'borrowable'                        => 'boolean',
            'requires_maintenance'              => 'boolean',
            'requires_inspection'               => 'boolean',
            'has_accessories'                   => 'boolean',
            'has_attachments'                   => 'boolean',
            'requires_operational_requirements' => 'boolean',
        ]);

        $checkboxes = [
            'serialized', 'borrowable', 'requires_maintenance', 
            'requires_inspection', 'has_accessories', 'has_attachments', 
            'requires_operational_requirements'
        ];
        foreach ($checkboxes as $cb) {
            $validated[$cb] = $request->boolean($cb);
        }

        $assetType->update($validated);

        return redirect()->route('assets.index', ['tab' => 'types'])
            ->with('success', 'Asset type updated successfully!');
    }

    /**
     * Remove the specified asset type from storage.
     */
    public function destroy(Request $request, AssetType $assetType)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        $assetType->delete();

        return redirect()->route('assets.index', ['tab' => 'types'])
            ->with('success', 'Asset type deleted successfully!');
    }
}
