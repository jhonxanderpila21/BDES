<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssetNeedRequest;
use App\Http\Requests\UpdateAssetNeedRequest;
use App\Models\Asset;
use App\Models\AssetNeed;
use App\Services\AssetNeedStatusService;
use Illuminate\Http\Request;

class AssetNeedController extends Controller
{
    /**
     * Display a listing of asset needs with optional filters.
     */
    public function index(Request $request)
    {
        $query = AssetNeed::with('asset');

        // Text search on requirement or asset name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('requirement', 'like', "%{$search}%")
                  ->orWhere('asset_type', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by asset_type
        if ($request->filled('asset_type')) {
            $query->where('asset_type', $request->asset_type);
        }

        // Filter by unit
        if ($request->filled('unit')) {
            $query->where('unit', $request->unit);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assetNeeds = $query->latest()->paginate(10)->withQueryString();

        // Data for filter dropdowns
        $assetTypes = AssetNeed::select('asset_type')->distinct()->orderBy('asset_type')->pluck('asset_type');
        $units = AssetNeedStatusService::units();
        $statuses = AssetNeedStatusService::statuses();

        // Assets for the add/edit form
        $assets = Asset::with('type')->orderBy('name')->get();

        return view('admin.asset-needs.index', compact(
            'assetNeeds',
            'assetTypes',
            'units',
            'statuses',
            'assets'
        ));
    }

    /**
     * Store a newly created asset need.
     */
    public function store(StoreAssetNeedRequest $request)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validated();

        // Auto-sync asset_type from the linked asset's type name
        $asset = Asset::with('type')->findOrFail($validated['asset_id']);
        $validated['asset_type'] = $asset->type->name ?? 'Unknown';

        AssetNeed::create($validated);

        return redirect()->route('asset-needs.index')
            ->with('success', 'Asset need created successfully!');
    }

    /**
     * Update the specified asset need.
     */
    public function update(UpdateAssetNeedRequest $request, AssetNeed $assetNeed)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validated();

        // Auto-sync asset_type from the linked asset's type name
        $asset = Asset::with('type')->findOrFail($validated['asset_id']);
        $validated['asset_type'] = $asset->type->name ?? 'Unknown';

        $assetNeed->update($validated);

        return redirect()->route('asset-needs.index')
            ->with('success', 'Asset need updated successfully!');
    }

    /**
     * Remove the specified asset need.
     */
    public function destroy(Request $request, AssetNeed $assetNeed)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        $assetNeed->delete();

        return redirect()->route('asset-needs.index')
            ->with('success', 'Asset need deleted successfully!');
    }

    /**
     * Replenish stock for an asset need.
     */
    public function replenish(Request $request, AssetNeed $assetNeed)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->has('amount') && is_string($request->amount)) {
            $request->merge(['amount' => trim(str_replace([',', ' '], '', $request->amount))]);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $assetNeed->replenish((float) $validated['amount']);

        return redirect()->route('asset-needs.index')
            ->with('success', "Replenished {$assetNeed->requirement} for {$assetNeed->asset_type}.");
    }
}
