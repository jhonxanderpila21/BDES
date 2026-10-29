<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAccessory;
use App\Models\AssetType;
use App\Models\Attachment;
use App\Services\AssetAccessoryService;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    /**
     * Display a listing of attachments and accessories in the unified hub.
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab');
        if (!in_array($activeTab, ['attachments', 'accessories'], true)) {
            $activeTab = ($request->routeIs('asset-accessories.*') || $request->routeIs('accessories.*'))
                ? 'accessories'
                : 'attachments';
        }

        // Global High-Level Metrics
        $metrics = [
            'total_attachments'      => Attachment::count(),
            'total_attachment_items' => Attachment::sum('quantity') ?: 0,
            'total_accessories'      => AssetAccessory::count(),
            'available_accessories'  => AssetAccessory::where('status', 'Available')->count(),
            'total_inventory_value'  => AssetAccessory::all()->sum('total_value'),
        ];

        // Reference Datasets
        $assets = Asset::with('type')->orderBy('name')->get();
        $assetTypes = AssetType::orderBy('name')->get();

        // 1. Attachments Data
        $attachmentsQuery = Attachment::with(['asset', 'assetType']);
        if ($request->filled('search') && $activeTab === 'attachments') {
            $search = $request->search;
            $attachmentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('type') && $activeTab === 'attachments') {
            $attachmentsQuery->where('type', $request->type);
        }
        if ($request->filled('condition') && $activeTab === 'attachments') {
            $attachmentsQuery->where('condition', $request->condition);
        }
        if ($request->filled('asset_id') && $activeTab === 'attachments') {
            $attachmentsQuery->where('asset_id', $request->asset_id);
        }
        if ($request->filled('asset_type_id') && $activeTab === 'attachments') {
            $attachmentsQuery->where('asset_type_id', $request->asset_type_id);
        }
        $attachments = $attachmentsQuery->latest('id')->paginate(10, ['*'], 'attachments_page')->withQueryString();

        // 2. Accessories Data
        $accessoriesQuery = AssetAccessory::with('asset');
        if ($request->filled('search') && $activeTab === 'accessories') {
            $search = $request->search;
            $accessoriesQuery->where(function ($q) use ($search) {
                $q->where('accessory_name', 'like', "%{$search}%")
                  ->orWhere('asset_type', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('asset_type') && $activeTab === 'accessories') {
            $accessoriesQuery->where('asset_type', $request->asset_type);
        }
        if ($request->filled('status') && $activeTab === 'accessories') {
            $accessoriesQuery->whereRaw('LOWER(status) = ?', [strtolower($request->status)]);
        }
        $accessories = $accessoriesQuery->latest('id')->paginate(10, ['*'], 'accessories_page')->withQueryString();

        // Accessories Supporting Data
        $budgetSummary = AssetAccessory::all()
            ->groupBy('asset_type')
            ->map(function ($items) {
                return $items->sum('total_value');
            });
        $accessoryAssetTypes = AssetAccessory::select('asset_type')->distinct()->orderBy('asset_type')->pluck('asset_type');
        $accessoryStatuses = AssetAccessoryService::statuses();

        return view('admin.attachments.index', compact(
            'activeTab',
            'metrics',
            'attachments',
            'accessories',
            'budgetSummary',
            'assets',
            'assetTypes',
            'accessoryAssetTypes',
            'accessoryStatuses'
        ));
    }

    /**
     * Store a newly created attachment in storage.
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
        if (isset($input['type'])) {
            $input['type'] = strip_tags(trim($input['type']));
        }
        $request->merge($input);

        $rules = array_merge([
            'name'          => 'required|string|max:255',
            'type'          => 'nullable|string|max:255',
            'asset_type_id' => 'nullable|exists:asset_types,id',
            'asset_id'      => 'nullable|exists:assets,id',
            'quantity'      => 'nullable|integer|min:0',
            'condition'     => 'nullable|string|in:excellent,good,fair,poor',
            'unit_cost'     => 'nullable|numeric|min:0',
        ], \App\Services\ReceiptService::rules());

        $validated = $request->validate($rules, \App\Services\ReceiptService::messages());

        if (empty($validated['quantity'])) {
            $validated['quantity'] = 1;
        }

        // If asset is selected but asset_type_id isn't, attempt to infer from asset
        if (!empty($validated['asset_id']) && empty($validated['asset_type_id'])) {
            $asset = Asset::find($validated['asset_id']);
            if ($asset && $asset->type_id) {
                $validated['asset_type_id'] = $asset->type_id;
            }
        }

        $receiptData = [
            'receipts' => $validated['receipts'] ?? null,
            'receipt_reference_number' => $validated['receipt_reference_number'] ?? null,
        ];
        unset($validated['receipts'], $validated['receipt_reference_number']);

        $attachment = Attachment::create($validated);
        \App\Services\ReceiptService::attachFromRequest($attachment, $request);

        return redirect()->route('attachments.index', ['tab' => 'attachments'])
            ->with('success', 'Attachment created successfully with supporting documents!');
    }

    /**
     * Update the specified attachment in storage.
     */
    public function update(Request $request, Attachment $attachment)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        $input = $request->all();
        if (isset($input['name'])) {
            $input['name'] = strip_tags(trim($input['name']));
        }
        if (isset($input['type'])) {
            $input['type'] = strip_tags(trim($input['type']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'nullable|string|max:255',
            'asset_type_id' => 'nullable|exists:asset_types,id',
            'asset_id'      => 'nullable|exists:assets,id',
            'quantity'      => 'nullable|integer|min:0',
            'condition'     => 'nullable|string|in:excellent,good,fair,poor',
        ]);

        if (!empty($validated['asset_id']) && empty($validated['asset_type_id'])) {
            $asset = Asset::find($validated['asset_id']);
            if ($asset && $asset->type_id) {
                $validated['asset_type_id'] = $asset->type_id;
            }
        }

        $attachment->update($validated);

        return redirect()->route('attachments.index', ['tab' => 'attachments'])
            ->with('success', 'Attachment updated successfully!');
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Request $request, Attachment $attachment)
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized action.');
        }

        $attachment->delete();

        return redirect()->route('attachments.index', ['tab' => 'attachments'])
            ->with('success', 'Attachment deleted successfully!');
    }
}
