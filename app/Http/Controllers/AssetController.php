<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use App\Services\AssetLifecycleService;
use App\Services\AuditTrailService;
use App\Services\PhilippineAccountingStandardService;
use App\Services\ReceiptService;
use App\Services\SkuGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetController extends Controller
{
    /**
     * Display a unified listing of assets, categories, and types.
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'assets');
        if (!in_array($activeTab, ['assets', 'categories', 'types'], true)) {
            $activeTab = 'assets';
        }

        // Enterprise KPI metrics
        $metrics = [
            'total_assets'              => Asset::count(),
            'available_assets'          => Asset::where('status', 'available')->count(),
            'deployed_assets'           => Asset::where('status', 'deployed')->count(),
            'under_maintenance_assets'  => Asset::where('status', 'under_maintenance')->count(),
            'out_of_service_assets'     => Asset::where('status', 'out_of_service')->count(),
            'lost_assets'               => Asset::where('status', 'lost')->count(),
            'retired_assets'            => Asset::where('status', 'retired')->count(),
            'total_acquisition_cost'    => Asset::sum('acquisition_cost') ?? 0,
            'total_categories'          => AssetCategory::count(),
            'total_types'               => AssetType::count(),
        ];

        // Dropdown reference datasets
        $categories = AssetCategory::orderBy('name')->get();
        $types = AssetType::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        // 1. Assets Inventory Tab Data
        $assetsQuery = Asset::with(['category', 'type', 'location', 'retiredBy']);

        if ($request->filled('search') && $activeTab === 'assets') {
            $search = $request->search;
            $assetsQuery->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category_id')) {
            $assetsQuery->where('category_id', $request->category_id);
        }
        if ($request->filled('type_id')) {
            $assetsQuery->where('type_id', $request->type_id);
        }
        if ($request->filled('location_id')) {
            $assetsQuery->where('location_id', $request->location_id);
        }
        if ($request->filled('condition')) {
            $assetsQuery->where('condition', $request->condition);
        }
        if ($request->filled('status') && $activeTab === 'assets') {
            $assetsQuery->where('status', $request->status);
        }

        // Sorting
        $allowedSorts = ['id', 'code', 'name', 'purchase_date', 'condition', 'status', 'acquisition_cost'];
        $sortBy = in_array($request->get('sort_by'), $allowedSorts, true) ? $request->get('sort_by') : 'id';
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $assets = $assetsQuery->orderBy($sortBy, $sortDir)->paginate(10, ['*'], 'assets_page')->withQueryString();

        // 2. Asset Categories Tab Data
        $categoriesQuery = AssetCategory::withCount('assets');
        if ($request->filled('search') && $activeTab === 'categories') {
            $search = $request->search;
            $categoriesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status') && $activeTab === 'categories') {
            $categoriesQuery->where('status', $request->status);
        }
        $categoriesList = $categoriesQuery->orderBy('name')->paginate(10, ['*'], 'categories_page')->withQueryString();

        // 3. Asset Types Tab Data
        $typesQuery = AssetType::with('category')->withCount('assets');
        if ($request->filled('search') && $activeTab === 'types') {
            $search = $request->search;
            $typesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($catQ) use ($search) {
                      $catQ->where('name', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('category_id') && $activeTab === 'types') {
            $typesQuery->where('category_id', $request->category_id);
        }
        $typesList = $typesQuery->orderBy('name')->paginate(10, ['*'], 'types_page')->withQueryString();

        return view('admin.assets.index', compact(
            'activeTab',
            'metrics',
            'assets',
            'categories',
            'types',
            'locations',
            'categoriesList',
            'typesList',
            'sortBy',
            'sortDir'
        ));
    }

    /**
     * Show the form for creating a new asset.
     */
    public function create()
    {
        return redirect()->route('assets.index', ['tab' => 'assets']);
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Asset::class);

        // Normalize inputs to get expected values
        $input = $request->all();
        if (isset($input['name'])) {
            $input['name'] = strip_tags(trim($input['name']));
        }
        if (isset($input['serial_number'])) {
            $input['serial_number'] = strtoupper(trim(strip_tags($input['serial_number'])));
        }
        if (isset($input['manufacturer'])) {
            $input['manufacturer'] = strip_tags(trim($input['manufacturer']));
        }
        if (isset($input['acquisition_cost']) && is_string($input['acquisition_cost'])) {
            $input['acquisition_cost'] = trim(str_replace([',', '₱', '$', 'PHP', ' '], '', $input['acquisition_cost']));
            if ($input['acquisition_cost'] === '') {
                $input['acquisition_cost'] = null;
            }
        }
        if (isset($input['notes'])) {
            $input['notes'] = strip_tags(trim($input['notes']));
        }
        $request->merge($input);

        $rules = array_merge([
            'name'             => ['required', 'string', 'max:255'],
            'category_id'      => ['required', 'exists:asset_categories,id'],
            'type_id'          => ['nullable', 'exists:asset_types,id'],
            'quantity'         => ['nullable', 'integer', 'min:1', 'max:100'],
            'serial_number'    => ['nullable', 'string', 'max:255'],
            'manufacturer'     => ['nullable', 'string', 'max:255'],
            'purchase_date'    => ['nullable', 'date'],
            'acquisition_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'warranty_expiry'  => ['nullable', 'date'],
            'location_id'      => ['required', 'exists:locations,id'],
            'condition'        => ['required', 'in:excellent,good,fair,poor'],
            'status'           => ['required', 'in:available,deployed,under_maintenance,out_of_service,lost,retired'],
            'notes'            => ['nullable', 'string'],
        ], ReceiptService::rules());

        $validated = $request->validate($rules, ReceiptService::messages());

        $quantity = (int) ($validated['quantity'] ?? 1);
        if ($quantity < 1) $quantity = 1;

        // Automate Philippine Government Standard accounting parameters (COA Circular No. 2022-004 & GAM for LGUs)
        $category = AssetCategory::find($validated['category_id']);
        $type = !empty($validated['type_id']) ? AssetType::find($validated['type_id']) : null;
        $validated = PhilippineAccountingStandardService::applyCoaAccountingParameters(
            $validated,
            $category?->name,
            $type?->name
        );

        unset($validated['quantity'], $validated['receipts'], $validated['receipt_reference_number']);

        $baseSerial = $validated['serial_number'] ?? null;

        $createdAssets = DB::transaction(function () use ($validated, $quantity, $category, $type, $baseSerial, $request) {
            $skus = SkuGenerator::generateBatch(
                $quantity,
                $validated['category_id'] ?? null,
                $validated['type_id'] ?? null,
                $validated['name'] ?? null
            );
            $assets = [];

            foreach ($skus as $index => $sku) {
                $recordData = $validated;
                $recordData['code'] = $sku;

                if (!empty($baseSerial)) {
                    $recordData['serial_number'] = $quantity > 1 ? "{$baseSerial}-" . ($index + 1) : $baseSerial;
                }

                $asset = Asset::create($recordData);

                if ($index === 0) {
                    ReceiptService::attachFromRequest($asset, $request);
                    $asset->load('receipts');
                } elseif (!empty($assets[0]) && $assets[0]->receipts->isNotEmpty()) {
                    ReceiptService::linkExisting($assets[0]->receipts, $asset);
                }

                AuditTrailService::log(
                    'created',
                    $asset,
                    "Asset [{$asset->name}] ({$asset->code}) registered into inventory.",
                    null,
                    $asset->only(['name', 'code', 'status', 'condition', 'category_id', 'location_id', 'acquisition_cost'])
                );

                $assets[] = $asset;
            }

            return $assets;
        });

        $count = count($createdAssets);
        $first = $createdAssets[0] ?? null;
        $skuList = collect($createdAssets)->pluck('code')->implode(', ');

        $message = $count > 1
            ? "Successfully registered {$count} assets: {$skuList}!"
            : "Asset [{$first?->name}] ({$first?->code}) registered successfully!";

        return redirect()->route('assets.index', ['tab' => 'assets'])
            ->with('success', $message);
    }

    /**
     * Display the specified asset with a complete 360-degree dossier.
     */
    public function show(Asset $asset)
    {
        $isAdmin = auth()->user()?->isAdmin();

        $asset->load([
            'category',
            'type',
            'location',
            'retiredBy',
            'maintenance.assignedUser',
            'inspections',
            'deployments.returnRecord',
            'deployments.approvedByUser',
            'returns.receivedByUser',
            'attachments',
            'accessories',
            'documents.uploader',
            'auditLogs' => function ($q) use ($isAdmin) {
                if (!$isAdmin) {
                    $q->where('user_id', auth()->id());
                }
            },
            'auditLogs.user',
            'reportedAssets'
        ]);

        $stats = [
            'total_maintenance_cost' => $asset->maintenance->sum('cost') ?? 0,
            'total_deployments'      => $asset->deployments->count(),
            'total_maintenances'     => $asset->maintenance->count(),
            'total_inspections'      => $asset->inspections->count(),
            'total_documents'        => $asset->documents->count(),
            'active_deployment'      => $asset->deployments->where('status', 'deployed')->sortByDesc('id')->first(),
        ];

        return view('admin.assets.show', [
            'asset' => $asset,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified asset.
     */
    public function edit(Asset $asset)
    {
        return redirect()->route('assets.index', ['tab' => 'assets']);
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        Gate::authorize('update', $asset);

        // Normalize inputs to get expected values
        $input = $request->all();
        if (isset($input['code'])) {
            $input['code'] = strtoupper(trim(strip_tags($input['code'])));
        }
        if (isset($input['name'])) {
            $input['name'] = strip_tags(trim($input['name']));
        }
        if (isset($input['serial_number'])) {
            $input['serial_number'] = strtoupper(trim(strip_tags($input['serial_number'])));
        }
        if (isset($input['manufacturer'])) {
            $input['manufacturer'] = strip_tags(trim($input['manufacturer']));
        }
        if (isset($input['acquisition_cost']) && is_string($input['acquisition_cost'])) {
            $input['acquisition_cost'] = trim(str_replace([',', '₱', '$', 'PHP', ' '], '', $input['acquisition_cost']));
            if ($input['acquisition_cost'] === '') {
                $input['acquisition_cost'] = null;
            }
        }
        if (isset($input['notes'])) {
            $input['notes'] = strip_tags(trim($input['notes']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'code'             => 'required|string|max:50|unique:assets,code,' . $asset->id,
            'name'             => 'required|string|max:255',
            'category_id'      => 'required|exists:asset_categories,id',
            'type_id'          => 'nullable|exists:asset_types,id',
            'serial_number'    => 'nullable|string|max:255',
            'manufacturer'     => 'nullable|string|max:255',
            'purchase_date'    => 'nullable|date',
            'acquisition_cost' => 'nullable|numeric|min:0|max:9999999999.99',
            'warranty_expiry'  => 'nullable|date',
            'location_id'      => 'required|exists:locations,id',
            'condition'        => 'required|in:excellent,good,fair,poor',
            'status'           => 'required|in:available,deployed,under_maintenance,out_of_service,lost,retired',
            'notes'            => 'nullable|string',
        ]);

        // Enforce lifecycle transition rules
        if ($validated['status'] !== $asset->status) {
            if (!AssetLifecycleService::canTransition($asset, $validated['status'])) {
                $statusLabels = AssetLifecycleService::statuses();
                $fromLabel = $statusLabels[$asset->status] ?? $asset->status;
                $toLabel   = $statusLabels[$validated['status']] ?? $validated['status'];
                throw ValidationException::withMessages([
                    'status' => "Invalid asset lifecycle transition: Cannot change status from [{$fromLabel}] to [{$toLabel}].",
                ]);
            }

            if ($validated['status'] === AssetLifecycleService::STATUS_RETIRED && !$asset->retirement_date) {
                $validated['retirement_date']   = now()->toDateString();
                $validated['retirement_reason'] = $validated['notes'] ?? 'Decommissioned via asset profile update.';
                $validated['retired_by']        = auth()->id();
            }
        }

        // Enforce accounting adjustments upon cost update
        if (isset($validated['acquisition_cost'])) {
            $newCost = (float) $validated['acquisition_cost'];
            if ($newCost < PhilippineAccountingStandardService::CAPITALIZATION_THRESHOLD) {
                $validated['salvage_value'] = 0.00;
                $validated['salvage_notes'] = 'Semi-Expendable Property (< ₱20,000.00 threshold). Non-depreciable; residual value set to ₱0.00.';
            } elseif (($asset->acquisition_cost === null || (float) $asset->acquisition_cost < PhilippineAccountingStandardService::CAPITALIZATION_THRESHOLD) && $newCost >= PhilippineAccountingStandardService::CAPITALIZATION_THRESHOLD) {
                // Newly capitalized asset: assign standard 10% residual value if not present
                if (empty($asset->salvage_value) || (float) $asset->salvage_value <= 0) {
                    $validated['salvage_value'] = round($newCost * PhilippineAccountingStandardService::DEFAULT_RESIDUAL_RATE, 2);
                    $validated['salvage_notes'] = 'Capitalized PPE (₱20,000.00+ threshold). Standard 10% residual value applied per COA Circular No. 2003-007.';
                }
            }
        }

        $oldValues = $asset->only(['name', 'code', 'status', 'condition', 'category_id', 'type_id', 'location_id', 'acquisition_cost', 'serial_number', 'manufacturer']);

        DB::transaction(function () use ($asset, $validated, $oldValues) {
            $asset->update($validated);
            $newValues = $asset->only(['name', 'code', 'status', 'condition', 'category_id', 'type_id', 'location_id', 'acquisition_cost', 'serial_number', 'manufacturer']);

            AuditTrailService::log(
                'updated',
                $asset,
                "Asset [{$asset->code}] profile updated.",
                $oldValues,
                $newValues
            );
        });

        return redirect()->route('assets.index', ['tab' => 'assets'])
            ->with('success', "Asset [{$asset->code}] updated successfully!");
    }

    /**
     * Formal decommissioning and retirement of an asset.
     */
    public function retire(Request $request, Asset $asset)
    {
        Gate::authorize('retire', $asset);

        $validated = $request->validate([
            'retirement_reason' => 'required|string|max:500',
        ]);

        AssetLifecycleService::retire($asset, $validated['retirement_reason'], auth()->user());

        return redirect()->back()->with('success', "Asset [{$asset->code}] has been formally decommissioned and retired.");
    }

    /**
     * Remove the specified asset from storage (safe delete or retire).
     */
    public function destroy(Request $request, Asset $asset)
    {
        Gate::authorize('delete', $asset);

        $wasDeleted = AssetLifecycleService::safeDeleteOrRetire(
            $asset,
            $request->input('reason', 'Asset deleted via registry interface.'),
            auth()->user()
        );

        if ($wasDeleted) {
            return redirect()->route('assets.index', ['tab' => 'assets'])
                ->with('success', "Asset [{$asset->code}] was permanently removed from registry.");
        }

        return redirect()->route('assets.index', ['tab' => 'assets'])
            ->with('info', "Asset [{$asset->code}] has linked operational records (deployments/maintenance/inspections) and has been decommissioned and retired instead of deleted to protect audit trail integrity.");
    }

    /**
     * Search assets
     */
    public function search(Request $request)
    {
        return redirect()->route('assets.index', [
            'tab'    => 'assets',
            'search' => $request->get('q'),
        ]);
    }

    /**
     * Export assets to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Asset::with(['category', 'type', 'location', 'retiredBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $filename = 'assets_inventory_' . now()->format('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($handle, [
                'Asset Code',
                'Asset Name',
                'Category',
                'Asset Type',
                'Serial Number',
                'Manufacturer',
                'Location',
                'Condition',
                'Status',
                'Acquisition Cost (PHP)',
                'Purchase Date',
                'Warranty Expiry',
                'Retirement Date',
                'Retirement Reason',
                'Retired By',
                'Registered At',
            ]);

            $query->chunk(200, function ($assets) use ($handle) {
                foreach ($assets as $asset) {
                    fputcsv($handle, [
                        $asset->code,
                        $asset->name,
                        $asset->category?->name ?? 'N/A',
                        $asset->type?->name ?? 'N/A',
                        $asset->serial_number ?? 'N/A',
                        $asset->manufacturer ?? 'N/A',
                        $asset->location?->name ?? 'N/A',
                        ucfirst($asset->condition),
                        AssetLifecycleService::statuses()[$asset->status] ?? ucfirst(str_replace('_', ' ', $asset->status)),
                        $asset->acquisition_cost ? number_format($asset->acquisition_cost, 2) : '0.00',
                        $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : 'N/A',
                        $asset->warranty_expiry ? $asset->warranty_expiry->format('Y-m-d') : 'N/A',
                        $asset->retirement_date ? $asset->retirement_date->format('Y-m-d') : 'N/A',
                        $asset->retirement_reason ?? 'N/A',
                        $asset->retiredBy?->name ?? 'N/A',
                        $asset->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
