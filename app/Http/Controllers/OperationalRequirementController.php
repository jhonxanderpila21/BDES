<?php

namespace App\Http\Controllers;

use App\Models\OperationalRequirement;
use App\Models\AssetType;
use Illuminate\Http\Request;

class OperationalRequirementController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->get('tab');
        if (!$activeTab) {
            $activeTab = $request->routeIs('asset-needs.*') ? 'needs' : 'all';
        }
        if (!in_array($activeTab, ['all', 'requirements', 'needs'], true)) {
            $activeTab = 'all';
        }

        // 1. Warehouse Consumables & Operational Requirements Query
        $reqQuery = OperationalRequirement::with([
            'assetType.assets' => function ($q) {
                $q->select('id', 'name', 'code', 'type_id', 'location_id', 'status', 'condition')
                  ->with('location:id,name');
            },
            'restocks',
        ]);

        if ($request->filled('search') && ($activeTab === 'all' || $activeTab === 'requirements')) {
            $search = $request->search;
            $reqQuery->where(function ($q) use ($search) {
                $q->where('requirement_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('assetType', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('asset_type_id') && ($activeTab === 'all' || $activeTab === 'requirements')) {
            $reqQuery->where('asset_type_id', $request->asset_type_id);
        }

        if ($request->filled('status') && ($activeTab === 'all' || $activeTab === 'requirements')) {
            if ($request->status === 'low') {
                $reqQuery->whereColumn('current_level', '<=', 'minimum_level');
            } elseif ($request->status === 'adequate') {
                $reqQuery->whereColumn('current_level', '>', 'minimum_level');
            }
        } elseif ($request->boolean('low') && ($activeTab === 'all' || $activeTab === 'requirements')) {
            $reqQuery->whereColumn('current_level', '<=', 'minimum_level');
        }

        $requirements = $reqQuery->latest('id')->paginate(10, ['*'], 'requirements_page')->withQueryString();
        $assetTypes = AssetType::where('name', 'not like', '%Fire Extinguisher%')->orderBy('name')->get();

        // 2. Equipment Supply Needs & Allocations Query
        $needsQuery = \App\Models\AssetNeed::with(['asset.type', 'asset.location']);

        if ($request->filled('search') && ($activeTab === 'all' || $activeTab === 'needs')) {
            $search = $request->search;
            $needsQuery->where(function ($q) use ($search) {
                $q->where('requirement', 'like', "%{$search}%")
                  ->orWhere('asset_type', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('asset_type') && ($activeTab === 'all' || $activeTab === 'needs')) {
            $needsQuery->where('asset_type', $request->asset_type);
        }

        if ($request->filled('unit') && ($activeTab === 'all' || $activeTab === 'needs')) {
            $needsQuery->where('unit', $request->unit);
        }

        if ($request->filled('status') && ($activeTab === 'all' || $activeTab === 'needs')) {
            if ($request->status === 'attention' || $request->status === 'low') {
                $needsQuery->whereIn('status', ['Requires Maintenance', 'Under Maintenance']);
            } else {
                $needsQuery->where('status', $request->status);
            }
        } elseif ($request->boolean('low') && ($activeTab === 'all' || $activeTab === 'needs')) {
            $needsQuery->whereIn('status', ['Requires Maintenance', 'Under Maintenance']);
        }

        $assetNeeds = $needsQuery->latest('id')->paginate(10, ['*'], 'needs_page')->withQueryString();
        $needAssetTypes = \App\Models\AssetNeed::select('asset_type')->distinct()->orderBy('asset_type')->pluck('asset_type');
        $units = \App\Services\AssetNeedStatusService::units();
        $statuses = \App\Services\AssetNeedStatusService::statuses();
        $assets = \App\Models\Asset::with('type')
            ->whereDoesntHave('type', function ($q) {
                $q->where('name', 'like', '%Fire Extinguisher%');
            })
            ->where('name', 'not like', '%Fire Extinguisher%')
            ->orderBy('name')
            ->get();

        // Build unified merged items collection
        $mergedCollection = collect();

        foreach ($requirements as $r) {
            $mergedCollection->push((object)[
                'id'               => $r->id,
                'source'           => 'requirement',
                'name'             => $r->requirement_type,
                'description'      => $r->description,
                'equipment_name'   => $r->assetType ? $r->assetType->name : 'General / Multi-Use',
                'current_level'    => (float) $r->current_level,
                'maximum_capacity' => (float) $r->maximum_capacity,
                'minimum_level'    => (float) $r->minimum_level,
                'unit'             => $r->unit,
                'is_low_stock'     => $r->isLowStock(),
                'status'           => $r->isLowStock() ? 'Low Stock' : 'In Stock',
                'raw_model'        => $r,
                'created_at'       => $r->created_at,
            ]);
        }

        foreach ($assetNeeds as $n) {
            $isLow = $n->isCritical() || in_array($n->status, ['Requires Maintenance', 'Under Maintenance']);
            $mergedCollection->push((object)[
                'id'               => $n->id,
                'source'           => 'need',
                'name'             => $n->requirement,
                'description'      => '',
                'equipment_name'   => $n->asset ? $n->asset->name : ($n->asset_type ?: 'General'),
                'current_level'    => (float) $n->current_level,
                'maximum_capacity' => (float) $n->maximum_capacity,
                'minimum_level'    => (float) $n->minimum_level,
                'unit'             => $n->unit,
                'is_low_stock'     => $isLow,
                'status'           => $n->status ?: ($isLow ? 'Requires Refill' : 'Monitoring Only'),
                'raw_model'        => $n,
                'created_at'       => $n->created_at,
            ]);
        }

        $sortedItems = $mergedCollection->sortByDesc('created_at')->values();

        // Paginate unified list
        $page = (int) $request->get('page', 1);
        $perPage = 15;
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedItems->forPage($page, $perPage)->values(),
            $sortedItems->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Calculate KPI summary metrics matching Asset Registry structure
        $allRequirements = OperationalRequirement::all();
        $allNeeds = \App\Models\AssetNeed::all();

        $metrics = [
            'total_all'          => $allRequirements->count() + $allNeeds->count(),
            'total_requirements' => $allRequirements->count(),
            'adequate_stock'     => $allRequirements->filter(fn($r) => !$r->isLowStock())->count()
                                    + $allNeeds->where('status', 'Monitoring Only')->count(),
            'low_stock'          => $allRequirements->filter(fn($r) => $r->isLowStock())->count(),
            'total_needs'        => $allNeeds->count(),
            'needs_monitoring'   => $allNeeds->where('status', 'Monitoring Only')->count(),
            'needs_attention'    => $allNeeds->whereIn('status', ['Requires Maintenance', 'Under Maintenance'])->count(),
            'total_low_stock'    => $allRequirements->filter(fn($r) => $r->isLowStock())->count() + $allNeeds->whereIn('status', ['Requires Maintenance', 'Under Maintenance'])->count(),
        ];

        return view('asset-management.operational-requirements.index', compact(
            'activeTab',
            'items',
            'requirements',
            'assetTypes',
            'assetNeeds',
            'needAssetTypes',
            'units',
            'statuses',
            'assets',
            'metrics'
        ));
    }

    public function create(Request $request)
    {
        $this->authorizeEditor($request);

        return redirect()->route('operational-requirements.index');
    }

    protected function authorizeEditor(Request $request): void
    {
        if ($request->user() && ($request->user()->isViewer() || $request->user()->hasRole('viewer', 'staff'))) {
            abort(403, 'Unauthorized. Operational editor permissions required.');
        }
    }

    public function store(Request $request)
    {
        $this->authorizeEditor($request);

        // Normalize text and numeric inputs
        $input = $request->all();
        if (isset($input['requirement_type'])) {
            $input['requirement_type'] = strip_tags(trim($input['requirement_type']));
        }
        if (isset($input['unit'])) {
            $input['unit'] = strip_tags(trim($input['unit']));
        }
        if (isset($input['description'])) {
            $input['description'] = strip_tags(trim($input['description']));
        }
        foreach (['current_level', 'maximum_capacity', 'minimum_level'] as $numField) {
            if (isset($input[$numField]) && is_string($input[$numField])) {
                $input[$numField] = trim(str_replace([',', ' '], '', $input[$numField]));
            }
        }
        $request->merge($input);

        $rules = array_merge([
            'asset_type_id'    => 'nullable|exists:asset_types,id',
            'requirement_type' => 'required|string|max:255',
            'unit'             => 'required|string|max:50',
            'maximum_capacity' => 'required|numeric|min:0.01',
            'minimum_level'    => 'required|numeric|min:0|lt:maximum_capacity',
            'current_level'    => 'required|numeric|min:0|lte:maximum_capacity',
            'description'      => 'nullable|string',
        ], \App\Services\ReceiptService::rules());

        $messages = array_merge([
            'minimum_level.lt'  => 'The minimum reserve level must be less than the maximum capacity.',
            'current_level.lte' => 'The current stock level cannot exceed the maximum capacity.',
        ], \App\Services\ReceiptService::messages());

        $validated = $request->validate($rules, $messages);
        unset($validated['receipts'], $validated['receipt_reference_number']);

        if (!empty($validated['asset_type_id'])) {
            $type = AssetType::find($validated['asset_type_id']);
            if ($type && str_contains(strtolower($type->name), 'fire extinguisher')) {
                return redirect()->route('operational-requirements.index')
                    ->withInput()
                    ->with('error', 'Fire extinguishers cannot have operational requirements or refills assigned.');
            }
        }
        if (str_contains(strtolower($validated['requirement_type']), 'fire extinguisher') || str_contains(strtolower($validated['requirement_type']), 'abc dry chemical')) {
            return redirect()->route('operational-requirements.index')
                ->withInput()
                ->with('error', 'Fire extinguisher refills cannot be logged as operational requirements.');
        }

        $requirement = OperationalRequirement::create($validated);
        \App\Services\ReceiptService::attachFromRequest($requirement, $request);

        return redirect()->route('operational-requirements.index')
            ->with('success', 'Operational requirement created successfully with supporting documents!');
    }

    public function show(Request $request, OperationalRequirement $operationalRequirement)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($operationalRequirement->load('assetType'));
        }

        return redirect()->route('operational-requirements.index', [
            'search' => $operationalRequirement->requirement_type,
            'view_requirement_id' => $operationalRequirement->id,
        ]);
    }

    public function edit(Request $request, OperationalRequirement $operationalRequirement)
    {
        $this->authorizeEditor($request);

        return redirect()->route('operational-requirements.index');
    }

    public function update(Request $request, OperationalRequirement $operationalRequirement)
    {
        $this->authorizeEditor($request);

        // Normalize text and numeric inputs
        $input = $request->all();
        if (isset($input['requirement_type'])) {
            $input['requirement_type'] = strip_tags(trim($input['requirement_type']));
        }
        if (isset($input['unit'])) {
            $input['unit'] = strip_tags(trim($input['unit']));
        }
        if (isset($input['description'])) {
            $input['description'] = strip_tags(trim($input['description']));
        }
        foreach (['current_level', 'maximum_capacity', 'minimum_level'] as $numField) {
            if (isset($input[$numField]) && is_string($input[$numField])) {
                $input[$numField] = trim(str_replace([',', ' '], '', $input[$numField]));
            }
        }
        $request->merge($input);

        $validated = $request->validate([
            'asset_type_id'    => 'nullable|exists:asset_types,id',
            'requirement_type' => 'required|string|max:255',
            'unit'             => 'required|string|max:50',
            'maximum_capacity' => 'required|numeric|min:0.01',
            'minimum_level'    => 'required|numeric|min:0|lt:maximum_capacity',
            'current_level'    => 'required|numeric|min:0|lte:maximum_capacity',
            'description'      => 'nullable|string',
        ], [
            'minimum_level.lt'  => 'The minimum reserve level must be less than the maximum capacity.',
            'current_level.lte' => 'The current stock level cannot exceed the maximum capacity.',
        ]);

        if (!empty($validated['asset_type_id'])) {
            $type = AssetType::find($validated['asset_type_id']);
            if ($type && str_contains(strtolower($type->name), 'fire extinguisher')) {
                return redirect()->route('operational-requirements.index')
                    ->withInput()
                    ->with('error', 'Fire extinguishers cannot have operational requirements or refills assigned.');
            }
        }
        if (str_contains(strtolower($validated['requirement_type']), 'fire extinguisher') || str_contains(strtolower($validated['requirement_type']), 'abc dry chemical')) {
            return redirect()->route('operational-requirements.index')
                ->withInput()
                ->with('error', 'Fire extinguisher refills cannot be logged as operational requirements.');
        }

        $operationalRequirement->update($validated);

        return redirect()->route('operational-requirements.index')
            ->with('success', 'Operational requirement updated successfully!');
    }

    public function destroy(Request $request, OperationalRequirement $operationalRequirement)
    {
        $this->authorizeEditor($request);

        $operationalRequirement->delete();

        return redirect()->route('operational-requirements.index')
            ->with('success', 'Operational requirement deleted successfully!');
    }

    public function refill(Request $request, OperationalRequirement $operationalRequirement)
    {
        $this->authorizeEditor($request);

        $operationalRequirement->load('assetType');
        $typeName = strtolower($operationalRequirement->assetType->name ?? '');
        $reqName = strtolower($operationalRequirement->requirement_type ?? '');
        if (str_contains($typeName, 'fire extinguisher') || str_contains($reqName, 'fire extinguisher') || str_contains($reqName, 'abc dry chemical')) {
            return redirect()->route('operational-requirements.index')
                ->with('error', 'Fire extinguishers cannot be refilled by barangay staff.');
        }

        if ($request->has('amount') && is_string($request->amount)) {
            $request->merge(['amount' => trim(str_replace([',', ' '], '', $request->amount))]);
        }
        if ($request->has('unit_cost') && is_string($request->unit_cost)) {
            $request->merge(['unit_cost' => trim(str_replace([',', ' '], '', $request->unit_cost))]);
        }

        $rules = array_merge([
            'amount' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ], \App\Services\ReceiptService::rules());

        $validated = $request->validate($rules, \App\Services\ReceiptService::messages());

        $qty = (float) $validated['amount'];
        $unitCost = (float) ($validated['unit_cost'] ?? 0);
        $totalCost = $qty * $unitCost;

        $restock = \App\Models\OperationalRequirementRestock::create([
            'operational_requirement_id' => $operationalRequirement->id,
            'quantity' => $qty,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'restocked_at' => now()->toDateString(),
            'supplier' => $validated['supplier'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => auth()->id(),
        ]);

        \App\Services\ReceiptService::attachFromRequest($restock, $request);

        $operationalRequirement->refill($qty);

        return redirect()->route('operational-requirements.index')
            ->with('success', "Added {$qty} {$operationalRequirement->unit} to {$operationalRequirement->requirement_type}. Current balance: {$operationalRequirement->fresh()->current_level} {$operationalRequirement->unit}.");
    }

    public function consume(Request $request, OperationalRequirement $operationalRequirement)
    {
        $this->authorizeEditor($request);

        if ($request->has('amount') && is_string($request->amount)) {
            $request->merge(['amount' => trim(str_replace([',', ' '], '', $request->amount))]);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $operationalRequirement->consume((float) $validated['amount']);

        return redirect()->route('operational-requirements.index')
            ->with('success', "Consumed from {$operationalRequirement->requirement_type}. Remaining balance: {$operationalRequirement->fresh()->current_level} {$operationalRequirement->unit}.");
    }
}
