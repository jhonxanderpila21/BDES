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
     * Authorize that the current user can manage asset needs.
     */
    protected function authorizeEditor(Request $request): void
    {
        if ($request->user() && ($request->user()->isViewer() || $request->user()->hasRole('viewer', 'staff'))) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Show the form for creating a new asset need (redirect to index for modal).
     */
    public function create(Request $request)
    {
        $this->authorizeEditor($request);

        return redirect()->route('asset-needs.index');
    }

    /**
     * Display the specified asset need.
     */
    public function show(Request $request, AssetNeed $assetNeed)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($assetNeed->load('asset'));
        }

        return redirect()->route('asset-needs.index', [
            'search' => $assetNeed->requirement,
            'view_need_id' => $assetNeed->id,
        ]);
    }

    /**
     * Show the form for editing the specified asset need (redirect to index for modal).
     */
    public function edit(Request $request, AssetNeed $assetNeed)
    {
        $this->authorizeEditor($request);

        return redirect()->route('asset-needs.index');
    }

    /**
     * Store a newly created asset need.
     */
    public function store(StoreAssetNeedRequest $request)
    {
        $this->authorizeEditor($request);

        $validated = $request->validated();

        // Auto-sync asset_type from the linked asset's type name
        $asset = Asset::with('type')->findOrFail($validated['asset_id']);
        $validated['asset_type'] = $asset->type->name ?? 'Unknown';

        unset($validated['receipts'], $validated['receipt_reference_number']);
        $need = AssetNeed::create($validated);
        \App\Services\ReceiptService::attachFromRequest($need, $request);

        return redirect()->route('asset-needs.index')
            ->with('success', 'Asset need created successfully!');
    }

    /**
     * Update the specified asset need.
     */
    public function update(UpdateAssetNeedRequest $request, AssetNeed $assetNeed)
    {
        $this->authorizeEditor($request);

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
        $this->authorizeEditor($request);

        $assetNeed->delete();

        return redirect()->route('asset-needs.index')
            ->with('success', 'Asset need deleted successfully!');
    }

    /**
     * Replenish stock for an asset need.
     */
    public function replenish(Request $request, AssetNeed $assetNeed)
    {
        $this->authorizeEditor($request);

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
