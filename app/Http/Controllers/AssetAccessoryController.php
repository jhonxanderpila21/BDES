<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssetAccessoryRequest;
use App\Http\Requests\UpdateAssetAccessoryRequest;
use App\Models\Asset;
use App\Models\AssetAccessory;
use App\Services\AssetAccessoryService;
use Illuminate\Http\Request;

class AssetAccessoryController extends Controller
{
    /**
     * Display a listing of the resource within the unified Attachments & Accessories hub.
     */
    public function index(Request $request)
    {
        return app(AttachmentController::class)->index($request->merge(['tab' => $request->get('tab', 'accessories')]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssetAccessoryRequest $request)
    {
        if ($request->user() && ($request->user()->isViewer() || $request->user()->hasRole('viewer', 'staff'))) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validated();
        
        $asset = Asset::with('type')->findOrFail($validated['asset_id']);
        $validated['asset_type'] = $asset->type->name ?? 'Unknown';

        $accessory = AssetAccessory::create($validated);
        \App\Services\ReceiptService::attachFromRequest($accessory, $request);

        return redirect()->route('asset-accessories.index')
            ->with('success', 'Asset accessory created successfully with supporting documents!');
    }

    /**
     * Display the specified asset accessory.
     */
    public function show(Request $request, AssetAccessory $assetAccessory)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($assetAccessory->load(['asset', 'receipts']));
        }

        return redirect()->route('attachments.index', [
            'tab' => 'accessories',
            'view_accessory_id' => $assetAccessory->id,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAssetAccessoryRequest $request, AssetAccessory $assetAccessory)
    {
        if ($request->user() && ($request->user()->isViewer() || $request->user()->hasRole('viewer', 'staff'))) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validated();

        $asset = Asset::with('type')->findOrFail($validated['asset_id']);
        $validated['asset_type'] = $asset->type->name ?? 'Unknown';

        $assetAccessory->update($validated);

        return redirect()->route('asset-accessories.index')
            ->with('success', 'Asset accessory updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, AssetAccessory $assetAccessory)
    {
        if ($request->user() && ($request->user()->isViewer() || $request->user()->hasRole('viewer', 'staff'))) {
            abort(403, 'Unauthorized action.');
        }

        $assetAccessory->delete();

        return redirect()->route('asset-accessories.index')
            ->with('success', 'Asset accessory deleted successfully!');
    }

    /**
     * Adjust the quantity (restock/consume).
     */
    public function adjustQuantity(Request $request, AssetAccessory $assetAccessory)
    {
        if ($request->user() && ($request->user()->isViewer() || $request->user()->hasRole('viewer', 'staff'))) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'action' => 'required|in:add,subtract',
            'amount' => 'required|integer|min:1',
        ]);

        if ($validated['action'] === 'add') {
            $assetAccessory->quantity += $validated['amount'];
        } else {
            if ($assetAccessory->quantity < $validated['amount']) {
                return redirect()->back()->withErrors(['amount' => 'Cannot subtract more than current inventory quantity.']);
            }
            $assetAccessory->quantity -= $validated['amount'];
        }

        $assetAccessory->save();

        $verb = $validated['action'] === 'add' ? 'restocked' : 'consumed';
        return redirect()->route('asset-accessories.index')
            ->with('success', "Accessory quantity {$verb} successfully!");
    }
}
