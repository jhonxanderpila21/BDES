<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\AssetType;
use App\Models\Supplier;
use Illuminate\Http\Request;

class AccessoryController extends Controller
{
    /**
     * Display a listing of accessories within the unified Attachments & Accessories hub.
     */
    public function index(Request $request)
    {
        return app(AttachmentController::class)->index($request->merge(['tab' => $request->get('tab', 'accessories')]));
    }

    /**
     * Store a newly created accessory in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'asset_type_id' => 'nullable|exists:asset_types,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity_in_stock' => 'required|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'status' => 'in:in_stock,low_stock,out_of_stock',
        ]);

        Accessory::create($validated);

        return redirect()->route('accessories.index')
            ->with('success', 'Accessory created successfully!');
    }

    /**
     * Update the specified accessory in storage.
     */
    public function update(Request $request, Accessory $accessory)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'asset_type_id' => 'nullable|exists:asset_types,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity_in_stock' => 'required|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'status' => 'in:in_stock,low_stock,out_of_stock',
        ]);

        $accessory->update($validated);

        return redirect()->route('accessories.index')
            ->with('success', 'Accessory updated successfully!');
    }

    /**
     * Remove the specified accessory from storage.
     */
    public function destroy(Accessory $accessory)
    {
        $accessory->delete();

        return redirect()->route('accessories.index')
            ->with('success', 'Accessory deleted successfully!');
    }
}
