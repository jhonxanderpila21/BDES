<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Services\AssetLifecycleService;
use App\Services\AuditTrailService;
use App\Services\MaintenanceScheduleService;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MaintenanceController extends Controller
{
    /**
     * Display the unified Maintenance Hub.
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'schedules');
        if (!in_array($activeTab, ['schedules', 'logs', 'inspections'], true)) {
            $activeTab = 'schedules';
        }

        // Dynamically refresh schedule statuses
        $allSchedules = MaintenanceSchedule::all();
        foreach ($allSchedules as $sched) {
            $sched->refreshStatus();
        }

        // High-level KPI metrics
        $metrics = [
            'total_schedules'    => MaintenanceSchedule::count(),
            'schedules_due'      => MaintenanceSchedule::whereIn('status', [
                MaintenanceScheduleService::STATUS_DUE,
                MaintenanceScheduleService::STATUS_OVERDUE,
            ])->count(),
            'active_maintenance'    => Maintenance::whereIn('status', ['in_progress', 'scheduled'])->count(),
            'completed_maintenance' => Maintenance::where('status', 'completed')->count(),
            'total_inspections'     => Inspection::count(),
            'total_repair_cost'     => Maintenance::sum('cost') ?? 0,
        ];

        // Assets and Users datasets
        $assets = Asset::with(['type', 'category'])->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        // 1. Preventive Schedules Tab Data
        $schedulesQuery = MaintenanceSchedule::with(['asset.type', 'asset.category']);
        if ($request->filled('search') && $activeTab === 'schedules') {
            $search = $request->search;
            $schedulesQuery->where(function ($q) use ($search) {
                $q->where('maintenance_task', 'like', "%{$search}%")
                  ->orWhere('asset_type', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('asset_type')) {
            $schedulesQuery->where('asset_type', $request->asset_type);
        }
        if ($request->filled('interval_type')) {
            $schedulesQuery->where('interval_type', $request->interval_type);
        }
        if ($request->filled('status') && $activeTab === 'schedules') {
            if ($request->status === 'due_overdue') {
                $schedulesQuery->whereIn('status', [
                    MaintenanceScheduleService::STATUS_DUE,
                    MaintenanceScheduleService::STATUS_OVERDUE,
                ]);
            } else {
                $schedulesQuery->where('status', $request->status);
            }
        }
        $schedules = $schedulesQuery->latest('id')->paginate(10, ['*'], 'schedules_page')->withQueryString();
        $assetTypes = MaintenanceSchedule::select('asset_type')->distinct()->orderBy('asset_type')->pluck('asset_type');
        $intervalTypes = MaintenanceScheduleService::intervalTypes();
        $scheduleStatuses = MaintenanceScheduleService::statuses();

        // 2. Maintenance Logs & Work Orders Tab Data
        $maintenancesQuery = Maintenance::with(['asset.category', 'assignedUser', 'receipts']);
        if ($request->filled('search') && $activeTab === 'logs') {
            $search = $request->search;
            $maintenancesQuery->where(function ($q) use ($search) {
                $q->where('work_order_number', 'like', "%{$search}%")
                  ->orWhere('technician', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('parts_used', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('type')) {
            $maintenancesQuery->where('type', $request->type);
        }
        if ($request->filled('priority')) {
            $maintenancesQuery->where('priority', $request->priority);
        }
        if ($request->filled('status') && $activeTab === 'logs') {
            if ($request->status === 'active') {
                $maintenancesQuery->whereIn('status', ['in_progress', 'scheduled']);
            } else {
                $maintenancesQuery->where('status', $request->status);
            }
        }
        $maintenances = $maintenancesQuery->latest('id')->paginate(10, ['*'], 'logs_page')->withQueryString();
        $maintenanceTypes = ['routine', 'preventive', 'repair', 'inspection', 'overhaul', 'emergency'];
        $maintenanceStatuses = ['scheduled', 'in_progress', 'completed', 'pending'];
        $priorityLevels = ['low', 'medium', 'high', 'urgent'];

        // 3. Inspections Tab Data
        $inspectionsQuery = Inspection::with(['asset.category']);
        if ($request->filled('search') && $activeTab === 'inspections') {
            $search = $request->search;
            $inspectionsQuery->where(function ($q) use ($search) {
                $q->where('inspector', 'like', "%{$search}%")
                  ->orWhere('findings', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('result')) {
            $inspectionsQuery->where('result', $request->result);
        }
        if ($request->filled('status') && $activeTab === 'inspections') {
            $inspectionsQuery->where('status', $request->status);
        }
        $inspections = $inspectionsQuery->latest('id')->paginate(10, ['*'], 'inspections_page')->withQueryString();
        $inspectionResults = ['passed', 'failed', 'conditional_pass'];
        $inspectionStatuses = ['scheduled', 'completed', 'overdue'];

        return view('admin.maintenance.index', compact(
            'activeTab',
            'metrics',
            'assets',
            'users',
            'schedules',
            'assetTypes',
            'intervalTypes',
            'scheduleStatuses',
            'maintenances',
            'maintenanceTypes',
            'maintenanceStatuses',
            'priorityLevels',
            'inspections',
            'inspectionResults',
            'inspectionStatuses'
        ));
    }

    /**
     * Store a newly created maintenance record / work order.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Maintenance::class);

        // Normalize text and cost inputs
        $input = $request->all();
        if (isset($input['cost']) && is_string($input['cost'])) {
            $input['cost'] = trim(str_replace([',', '₱', '$', 'PHP', ' '], '', $input['cost']));
            if ($input['cost'] === '') {
                $input['cost'] = null;
            }
        }
        if (isset($input['technician'])) {
            $input['technician'] = strip_tags(trim($input['technician']));
        }
        if (empty($input['technician'])) {
            $input['technician'] = auth()->user()?->name;
        }
        if (isset($input['parts_used'])) {
            $input['parts_used'] = strip_tags(trim($input['parts_used']));
        }
        if (isset($input['notes'])) {
            $input['notes'] = strip_tags(trim($input['notes']));
        }
        if (empty($input['assigned_to'])) {
            $input['assigned_to'] = auth()->id();
        }
        $request->merge($input);

        $rules = array_merge([
            'asset_id'           => 'required|exists:assets,id',
            'type'               => 'required|in:routine,preventive,repair,inspection,overhaul,emergency',
            'priority'           => 'nullable|in:low,medium,high,urgent',
            'technician'         => 'nullable|string|max:255',
            'assigned_to'        => 'nullable|exists:users,id',
            'scheduled_date'     => 'nullable|date',
            'estimated_duration' => 'nullable|string|max:255',
            'cost'               => 'nullable|numeric|min:0',
            'parts_used'         => 'nullable|string|max:500',
            'notes'              => 'nullable|string',
            'status'             => 'required|in:scheduled,in_progress,completed,pending',
        ], ReceiptService::rules());

        $validated = $request->validate($rules, ReceiptService::messages());

        if (empty($validated['priority'])) {
            $validated['priority'] = 'medium';
        }

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $maintenance = DB::transaction(function () use ($validated, $request) {
            $created = Maintenance::create($validated);
            ReceiptService::attachFromRequest($created, $request);
            $asset = Asset::find($validated['asset_id']);

            if ($asset && $validated['status'] === 'in_progress') {
                if (AssetLifecycleService::canTransition($asset, AssetLifecycleService::STATUS_UNDER_MAINTENANCE)) {
                    AssetLifecycleService::transition($asset, AssetLifecycleService::STATUS_UNDER_MAINTENANCE, "Work order [{$created->work_order_number}] started.");
                }
            }

            AuditTrailService::log(
                'created',
                $created,
                "Work Order [{$created->work_order_number}] ({$created->type}) opened for asset [{$asset?->code}].",
                null,
                $created->only(['work_order_number', 'asset_id', 'type', 'priority', 'status', 'cost'])
            );

            return $created;
        });

        return redirect()->route('maintenance.index', ['tab' => 'logs'])
            ->with('success', "Work Order [{$maintenance->work_order_number}] created successfully!");
    }

    /**
     * Update the specified maintenance record.
     */
    public function update(Request $request, Maintenance $maintenance)
    {
        Gate::authorize('update', $maintenance);

        // Normalize text and cost inputs
        $input = $request->all();
        if (isset($input['cost']) && is_string($input['cost'])) {
            $input['cost'] = trim(str_replace([',', '₱', '$', 'PHP', ' '], '', $input['cost']));
            if ($input['cost'] === '') {
                $input['cost'] = null;
            }
        }
        if (isset($input['technician'])) {
            $input['technician'] = strip_tags(trim($input['technician']));
        }
        if (isset($input['parts_used'])) {
            $input['parts_used'] = strip_tags(trim($input['parts_used']));
        }
        if (isset($input['notes'])) {
            $input['notes'] = strip_tags(trim($input['notes']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'asset_id'           => 'required|exists:assets,id',
            'type'               => 'required|in:routine,preventive,repair,inspection,overhaul,emergency',
            'priority'           => 'nullable|in:low,medium,high,urgent',
            'technician'         => 'nullable|string|max:255',
            'assigned_to'        => 'nullable|exists:users,id',
            'scheduled_date'     => 'nullable|date',
            'estimated_duration' => 'nullable|string|max:255',
            'cost'               => 'nullable|numeric|min:0',
            'parts_used'         => 'nullable|string|max:500',
            'notes'              => 'nullable|string',
            'status'             => 'required|in:scheduled,in_progress,completed,pending',
        ]);

        if (empty($validated['priority'])) {
            $validated['priority'] = 'medium';
        }

        if ($validated['status'] === 'completed' && empty($maintenance->completed_at)) {
            $validated['completed_at'] = now();
        }

        DB::transaction(function () use ($validated, $maintenance) {
            $oldValues = $maintenance->only(['type', 'priority', 'technician', 'assigned_to', 'status', 'cost', 'parts_used']);
            $maintenance->update($validated);
            $newValues = $maintenance->only(['type', 'priority', 'technician', 'assigned_to', 'status', 'cost', 'parts_used']);

            $asset = Asset::find($validated['asset_id']);
            if ($asset) {
                if ($validated['status'] === 'in_progress' && $asset->status !== AssetLifecycleService::STATUS_UNDER_MAINTENANCE) {
                    if (AssetLifecycleService::canTransition($asset, AssetLifecycleService::STATUS_UNDER_MAINTENANCE)) {
                        AssetLifecycleService::transition($asset, AssetLifecycleService::STATUS_UNDER_MAINTENANCE, "Work order [{$maintenance->work_order_number}] in progress.");
                    }
                } elseif ($validated['status'] === 'completed' && $asset->status === AssetLifecycleService::STATUS_UNDER_MAINTENANCE) {
                    $hasOther = Maintenance::where('asset_id', $asset->id)
                        ->where('id', '!=', $maintenance->id)
                        ->where('status', 'in_progress')
                        ->exists();

                    if (!$hasOther && AssetLifecycleService::canTransition($asset, AssetLifecycleService::STATUS_AVAILABLE)) {
                        AssetLifecycleService::transition($asset, AssetLifecycleService::STATUS_AVAILABLE, "Work order [{$maintenance->work_order_number}] completed.");
                    }
                }
            }

            AuditTrailService::log(
                'updated',
                $maintenance,
                "Work Order [{$maintenance->work_order_number}] updated.",
                $oldValues,
                $newValues
            );
        });

        return redirect()->route('maintenance.index', ['tab' => 'logs'])
            ->with('success', "Work Order [{$maintenance->work_order_number}] updated successfully!");
    }

    /**
     * Mark a maintenance work order as completed.
     */
    public function complete(Request $request, Maintenance $maintenance)
    {
        Gate::authorize('complete', $maintenance);

        $input = $request->all();
        if (isset($input['cost']) && is_string($input['cost'])) {
            $input['cost'] = trim(str_replace([',', '₱', '$', 'PHP', ' '], '', $input['cost']));
            if ($input['cost'] === '') {
                $input['cost'] = null;
            }
        }
        if (isset($input['parts_used'])) {
            $input['parts_used'] = strip_tags(trim($input['parts_used']));
        }
        if (isset($input['notes'])) {
            $input['notes'] = strip_tags(trim($input['notes']));
        }
        $request->merge($input);

        $rules = array_merge([
            'cost'       => 'nullable|numeric|min:0',
            'parts_used' => 'nullable|string|max:500',
            'notes'      => 'nullable|string',
        ], ReceiptService::rules());

        $validated = $request->validate($rules, ReceiptService::messages());

        DB::transaction(function () use ($maintenance, $validated, $request) {
            $updateData = [
                'status'       => 'completed',
                'completed_at' => now(),
            ];

            if (isset($validated['cost']) && $validated['cost'] !== null) {
                $updateData['cost'] = $validated['cost'];
            }
            if (!empty($validated['parts_used'])) {
                $updateData['parts_used'] = $validated['parts_used'];
            }
            if (!empty($validated['notes'])) {
                $updateData['notes'] = $maintenance->notes ? ($maintenance->notes . "\n[Completion Notes]: " . $validated['notes']) : $validated['notes'];
            }

            $maintenance->update($updateData);
            ReceiptService::attachFromRequest($maintenance, $request);

            $asset = $maintenance->asset;
            if ($asset && $asset->status === AssetLifecycleService::STATUS_UNDER_MAINTENANCE) {
                $hasOther = Maintenance::where('asset_id', $asset->id)
                    ->where('id', '!=', $maintenance->id)
                    ->where('status', 'in_progress')
                    ->exists();

                if (!$hasOther && AssetLifecycleService::canTransition($asset, AssetLifecycleService::STATUS_AVAILABLE)) {
                    AssetLifecycleService::transition($asset, AssetLifecycleService::STATUS_AVAILABLE, "Work order [{$maintenance->work_order_number}] completed.");
                }
            }

            AuditTrailService::log(
                'status_changed',
                $maintenance,
                "Work Order [{$maintenance->work_order_number}] signed off as completed. Final cost: ₱" . number_format($maintenance->cost ?? 0, 2),
                ['status' => 'in_progress'],
                ['status' => 'completed', 'completed_at' => $maintenance->completed_at]
            );
        });

        return redirect()->route('maintenance.index', ['tab' => 'logs'])
            ->with('success', "Work Order [{$maintenance->work_order_number}] completed and signed off!");
    }

    /**
     * Remove the specified maintenance record.
     */
    public function destroy(Maintenance $maintenance)
    {
        Gate::authorize('delete', $maintenance);

        DB::transaction(function () use ($maintenance) {
            $asset = $maintenance->asset;
            $status = $maintenance->status;
            $woNumber = $maintenance->work_order_number;

            AuditTrailService::log('deleted', $maintenance, "Work Order [{$woNumber}] deleted.");
            $maintenance->delete();

            if ($status === 'in_progress' && $asset && $asset->status === AssetLifecycleService::STATUS_UNDER_MAINTENANCE) {
                $hasOther = Maintenance::where('asset_id', $asset->id)->where('status', 'in_progress')->exists();
                if (!$hasOther && AssetLifecycleService::canTransition($asset, AssetLifecycleService::STATUS_AVAILABLE)) {
                    AssetLifecycleService::transition($asset, AssetLifecycleService::STATUS_AVAILABLE, "Active work order [{$woNumber}] was deleted.");
                }
            }
        });

        return redirect()->route('maintenance.index', ['tab' => 'logs'])
            ->with('success', 'Maintenance record deleted successfully!');
    }
}
