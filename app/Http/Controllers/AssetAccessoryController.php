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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AssetAccessory::with('asset');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('accessory_name', 'like', "%{$search}%")
                  ->orWhere('asset_type', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('asset_type')) {
            $query->where('asset_type', $request->asset_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $accessories = $query->latest()->paginate(10)->withQueryString();

        $assetTypes = AssetAccessory::select('asset_type')->distinct()->orderBy('asset_type')->pluck('asset_type');
        $statuses = AssetAccessoryService::statuses();
        $assets = Asset::with('type')->orderBy('name')->get();

        // Calculate budgeting summary
        $budgetSummary = AssetAccessory::all()
            ->groupBy('asset_type')
            ->map(function ($items) {
                return $items->sum('total_value');
            });

        return view('admin.asset-accessories.index', compact(
            'accessories',
            'assetTypes',
            'statuses',
            'assets',
            'budgetSummary'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssetAccessoryRequest $request)
    {
        $validated = $request->validated();
        
        $asset = Asset::with('type')->findOrFail($validated['asset_id']);
        $validated['asset_type'] = $asset->type->name ?? 'Unknown';

        AssetAccessory::create($validated);

        return redirect()->route('asset-accessories.index')
            ->with('success', 'Asset accessory created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAssetAccessoryRequest $request, AssetAccessory $assetAccessory)
    {
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
    public function destroy(AssetAccessory $assetAccessory)
    {
        $assetAccessory->delete();

        return redirect()->route('asset-accessories.index')
            ->with('success', 'Asset accessory deleted successfully!');
    }

    /**
     * Adjust the quantity (restock/consume).
     */
    public function adjustQuantity(Request $request, AssetAccessory $assetAccessory)
    {
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
