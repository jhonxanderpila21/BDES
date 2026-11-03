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
     * Display a listing of attachments and accessories in the unified Asset Components and Parts hub.
     */
    public function index(Request $request)
    {
        $requestedTab = $request->get('tab');
        if (!in_array($requestedTab, ['all', 'attachments', 'accessories'], true)) {
            $activeTab = ($request->routeIs('asset-accessories.*') || $request->routeIs('accessories.*'))
                ? 'accessories'
                : 'all';
        } else {
            $activeTab = $requestedTab;
        }

        // Global High-Level Metrics
        $totalAttachments = Attachment::count();
        $totalAttachmentItems = (int) Attachment::sum('quantity');
        $totalAccessories = AssetAccessory::count();
        $availableAccessories = AssetAccessory::where('status', 'Available')->count();
        $availableAttachments = Attachment::where('quantity', '>', 0)->count();
        $totalAccessoriesUnits = (int) AssetAccessory::sum('quantity');
        $totalInventoryValue = AssetAccessory::all()->sum('total_value');

        $metrics = [
            'total_components'       => $totalAttachments + $totalAccessories,
            'total_attachments'      => $totalAttachments,
            'total_attachment_items' => $totalAttachmentItems,
            'total_accessories'      => $totalAccessories,
            'available_accessories'  => $availableAccessories,
            'available_components'   => $availableAttachments + $availableAccessories,
            'total_available_units'  => $totalAttachmentItems + $totalAccessoriesUnits,
            'total_inventory_value'  => $totalInventoryValue,
        ];

        // Reference Datasets
        $assets = Asset::with('type')->orderBy('name')->get();
        $assetTypes = AssetType::orderBy('name')->get();

        // 1. Attachments Data Query
        $attachmentsQuery = Attachment::with(['asset', 'assetType']);
        if ($request->filled('search')) {
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
        if ($request->filled('type')) {
            $attachmentsQuery->where('type', $request->type);
        }
        if ($request->filled('condition')) {
            $attachmentsQuery->where('condition', $request->condition);
        }
        if ($request->filled('asset_id')) {
            $attachmentsQuery->where('asset_id', $request->asset_id);
        }
        if ($request->filled('asset_type_id')) {
            $attachmentsQuery->where('asset_type_id', $request->asset_type_id);
        }
        if ($request->filled('status')) {
            if (strtolower($request->status) === 'available') {
                $attachmentsQuery->where('quantity', '>', 0);
            } elseif (strtolower($request->status) === 'not available') {
                $attachmentsQuery->where('quantity', '<=', 0);
            }
        }
        if ($activeTab === 'accessories') {
            $attachmentsQuery->whereRaw('1 = 0');
        }

        // 2. Accessories Data Query
        $accessoriesQuery = AssetAccessory::with('asset');
        if ($request->filled('search')) {
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
        if ($request->filled('asset_type')) {
            $accessoriesQuery->where('asset_type', $request->asset_type);
        }
        if ($request->filled('type') && $request->type !== 'Accessory') {
            $accessoriesQuery->whereRaw('1 = 0');
        }
        if ($request->filled('condition')) {
            $accessoriesQuery->whereRaw('1 = 0');
        }
        if ($request->filled('status')) {
            $accessoriesQuery->whereRaw('LOWER(status) = ?', [strtolower($request->status)]);
        }
        if ($request->filled('asset_id')) {
            $accessoriesQuery->where('asset_id', $request->asset_id);
        }
        if ($activeTab === 'attachments') {
            $accessoriesQuery->whereRaw('1 = 0');
        }

        // Sorting & Pagination handling
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortBy = $request->get('sort_by');

        if ($activeTab === 'attachments') {
            $allowedSorts = ['id', 'name', 'type', 'quantity', 'condition', 'created_at'];
            $sortColumn = in_array($sortBy, $allowedSorts, true) ? $sortBy : 'id';
            $attachments = $attachmentsQuery->orderBy($sortColumn, $sortDir)->paginate(10, ['*'], 'attachments_page')->withQueryString();
            $accessories = AssetAccessory::with('asset')->latest('id')->paginate(10, ['*'], 'accessories_page');
            $items = $attachments;
        } elseif ($activeTab === 'accessories') {
            $allowedAccessorySorts = ['id', 'accessory_name', 'asset_type', 'quantity', 'unit_cost', 'status', 'created_at'];
            $accSortColumn = in_array($sortBy, $allowedAccessorySorts, true) ? $sortBy : 'id';
            $accessories = $accessoriesQuery->orderBy($accSortColumn, $sortDir)->paginate(10, ['*'], 'accessories_page')->withQueryString();
            $attachments = Attachment::with(['asset', 'assetType'])->latest('id')->paginate(10, ['*'], 'attachments_page');
            $items = $accessories;
        } else {
            // 'all' tab: Merge both into unified collection
            $attList = $attachmentsQuery->latest('id')->get()->map(function ($att) {
                $att->is_accessory = false;
                $att->display_name = $att->name;
                $att->classification = $att->type ?: 'Attachment';
                $att->for_asset_type = $att->assetType->name ?? 'Any';
                $att->sort_date = $att->created_at ? $att->created_at->timestamp : $att->id;
                return $att;
            });

            $accList = $accessoriesQuery->latest('id')->get()->map(function ($acc) {
                $acc->is_accessory = true;
                $acc->display_name = $acc->accessory_name;
                $acc->classification = 'Accessory';
                $acc->for_asset_type = $acc->asset_type ?: 'Any';
                $acc->sort_date = $acc->created_at ? $acc->created_at->timestamp : $acc->id;
                return $acc;
            });

            $mergedCollection = $attList->concat($accList);

            if ($sortBy === 'name' || $sortBy === 'accessory_name') {
                $mergedCollection = $sortDir === 'asc'
                    ? $mergedCollection->sortBy(fn($i) => strtolower($i->display_name))->values()
                    : $mergedCollection->sortByDesc(fn($i) => strtolower($i->display_name))->values();
            } elseif ($sortBy === 'quantity') {
                $mergedCollection = $sortDir === 'asc'
                    ? $mergedCollection->sortBy('quantity')->values()
                    : $mergedCollection->sortByDesc('quantity')->values();
            } else {
                $mergedCollection = $sortDir === 'asc'
                    ? $mergedCollection->sortBy('sort_date')->values()
                    : $mergedCollection->sortByDesc('sort_date')->values();
            }

            $page = (int) $request->get('page', 1);
            $perPage = 10;
            $items = new \Illuminate\Pagination\LengthAwarePaginator(
                $mergedCollection->forPage($page, $perPage)->values(),
                $mergedCollection->count(),
                $perPage,
                $page,
                [
                    'path' => route('attachments.index'),
                    'pageName' => 'page',
                    'query' => $request->query(),
                ]
            );

            $attachments = Attachment::with(['asset', 'assetType'])->latest('id')->paginate(10, ['*'], 'attachments_page');
            $accessories = AssetAccessory::with('asset')->latest('id')->paginate(10, ['*'], 'accessories_page');
        }

        // Accessories Supporting Data
        $budgetSummary = AssetAccessory::all()
            ->groupBy('asset_type')
            ->map(function ($itemGroup) {
                return $itemGroup->sum('total_value');
            });
        $accessoryAssetTypes = AssetAccessory::select('asset_type')->distinct()->orderBy('asset_type')->pluck('asset_type');
        $accessoryStatuses = AssetAccessoryService::statuses();

        return view('asset-management.components-parts.index', compact(
            'activeTab',
            'metrics',
            'items',
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
     * Show the form for creating a new attachment.
     */
    public function create(Request $request)
    {
        if ($request->user() && ($request->user()->isViewer() || $request->user()->hasRole('viewer', 'staff'))) {
            abort(403, 'Unauthorized action.');
        }

        return redirect()->route('attachments.index', ['tab' => 'attachments']);
    }

    /**
     * Store a newly created attachment in storage.
     */
    public function store(Request $request)
    {
        if ($request->user() && ($request->user()->isViewer() || $request->user()->hasRole('viewer', 'staff'))) {
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
     * Display the specified attachment.
     */
    public function show(Request $request, Attachment $attachment)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($attachment->load(['asset', 'assetType', 'receipts']));
        }

        return redirect()->route('attachments.index', [
            'tab' => 'attachments',
            'view_attachment_id' => $attachment->id,
        ]);
    }

    /**
     * Show the form for editing the specified attachment.
     */
    public function edit(Request $request, Attachment $attachment)
    {
        if ($request->user() && ($request->user()->isViewer() || $request->user()->hasRole('viewer', 'staff'))) {
            abort(403, 'Unauthorized action.');
        }

        return redirect()->route('attachments.index', ['tab' => 'attachments']);
    }

    /**
     * Update the specified attachment in storage.
     */
    public function update(Request $request, Attachment $attachment)
    {
        if ($request->user() && ($request->user()->isViewer() || $request->user()->hasRole('viewer', 'staff'))) {
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
        if ($request->user() && ($request->user()->isViewer() || $request->user()->hasRole('viewer', 'staff'))) {
            abort(403, 'Unauthorized action.');
        }

        $attachment->delete();

        return redirect()->route('attachments.index', ['tab' => 'attachments'])
            ->with('success', 'Attachment deleted successfully!');
    }
}
