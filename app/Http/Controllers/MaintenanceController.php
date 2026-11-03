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
        $requestedTab = $request->get('tab');
        if ($requestedTab === 'schedules') {
            $activeTab = 'logs';
        } else {
            $activeTab = $request->get('tab', 'logs');
        }
        if (!in_array($activeTab, ['logs', 'inspections'], true)) {
            $activeTab = 'logs';
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

        // 1. Preventive Schedules Data
        $schedulesQuery = MaintenanceSchedule::with(['asset.type', 'asset.category', 'receipts']);
        if ($request->filled('schedule_search') || ($request->filled('search') && $activeTab === 'logs')) {
            $search = $request->get('schedule_search', $request->search);
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
        if ($request->filled('type') && !in_array($request->type, ['preventive', 'routine'], true)) {
            $schedulesQuery->whereRaw('1 = 0');
        }
        if ($request->filled('schedule_status')) {
            $schedulesQuery->where('status', $request->schedule_status);
        } elseif ($request->filled('status')) {
            if ($request->status === 'due_overdue') {
                $schedulesQuery->whereIn('status', [
                    MaintenanceScheduleService::STATUS_DUE,
                    MaintenanceScheduleService::STATUS_OVERDUE,
                ]);
            } elseif (in_array($request->status, ['due', 'overdue', 'Monitoring Only'], true)) {
                $schedulesQuery->where('status', $request->status);
            } elseif ($request->status === 'active') {
                $schedulesQuery->whereIn('status', [
                    MaintenanceScheduleService::STATUS_OK,
                    MaintenanceScheduleService::STATUS_DUE,
                ]);
            } elseif (in_array($request->status, ['completed', 'pending'], true)) {
                $schedulesQuery->whereRaw('1 = 0');
            }
        }
        $schedules = $schedulesQuery->latest('id')->paginate(10, ['*'], 'schedules_page')->withQueryString();
        $assetTypes = MaintenanceSchedule::select('asset_type')->distinct()->orderBy('asset_type')->pluck('asset_type');
        $intervalTypes = MaintenanceScheduleService::intervalTypes();
        $scheduleStatuses = MaintenanceScheduleService::statuses();

        // 2. Maintenance Logs & Work Orders Tab Data
        $maintenancesQuery = Maintenance::with([
            'asset.category',
            'asset.type',
            'assignedUser',
            'assignedPersonnel',
            'confirmedByUser',
            'verifiedByUser',
            'inspection',
            'parts',
            'receipts',
        ]);
        if ($request->filled('search') && $activeTab === 'logs') {
            $search = $request->search;
            $maintenancesQuery->where(function ($q) use ($search) {
                $q->where('work_order_number', 'like', "%{$search}%")
                  ->orWhere('technician', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('parts_used', 'like', "%{$search}%")
                  ->orWhereHas('parts', function ($pq) use ($search) {
                      $pq->where('part_name', 'like', "%{$search}%");
                  })
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
            } elseif ($request->status === 'due_overdue') {
                $maintenancesQuery->whereRaw('1 = 0');
            } elseif (!in_array($request->status, ['Monitoring Only', 'Maintenance Due', 'Overdue'], true)) {
                $maintenancesQuery->where('status', $request->status);
            } else {
                $maintenancesQuery->whereRaw('1 = 0');
            }
        }

        // Record type classification: all, work_orders, schedules
        $recordType = $request->get('record_type', 'all');

        // Merge Work Orders and Schedules into unified collection for the maintenance table
        if ($recordType === 'schedules') {
            $workOrders = collect();
        } else {
            $workOrders = $maintenancesQuery->latest('id')->get()->map(function ($m) {
                $m->is_schedule = false;
                $m->record_type = 'WO';
                $m->sort_date = $m->scheduled_date ? \Carbon\Carbon::parse($m->scheduled_date)->timestamp : $m->created_at->timestamp;
                return $m;
            });
        }

        if ($recordType === 'work_orders') {
            $scheduleItems = collect();
        } else {
            $scheduleItems = $schedulesQuery->latest('id')->get()->map(function ($s) {
                $s->is_schedule = true;
                $s->record_type = 'SCHED';
                $s->sort_date = $s->next_due_date ? \Carbon\Carbon::parse($s->next_due_date)->timestamp : $s->created_at->timestamp;
                return $s;
            });
        }

        $mergedCollection = $workOrders->concat($scheduleItems)->sortByDesc('sort_date')->values();
        $page = (int) $request->get('logs_page', 1);
        $perPage = 10;
        $maintenances = new \Illuminate\Pagination\LengthAwarePaginator(
            $mergedCollection->forPage($page, $perPage)->values(),
            $mergedCollection->count(),
            $perPage,
            $page,
            [
                'path' => route('maintenance.index'),
                'pageName' => 'logs_page',
                'query' => $request->query(),
            ]
        );
        $maintenanceTypes = ['routine', 'preventive', 'repair', 'inspection', 'overhaul', 'emergency'];
        $maintenanceStatuses = ['scheduled', 'in_progress', 'completed', 'pending'];
        $priorityLevels = ['low', 'medium', 'high', 'urgent'];

        // 3. Inspections Tab Data
        $inspectionsQuery = Inspection::with(['asset.category', 'asset.type', 'workOrders', 'receipts']);
        if ($request->filled('inspection_search') || ($request->filled('search') && $activeTab === 'inspections')) {
            $search = $request->get('inspection_search', $request->search);
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
        if ($request->filled('inspection_status')) {
            $inspectionsQuery->where('status', $request->inspection_status);
        } elseif ($request->filled('status') && $activeTab === 'inspections' && in_array($request->status, ['completed', 'scheduled', 'overdue'], true)) {
            $inspectionsQuery->where('status', $request->status);
        }
        $inspections = $inspectionsQuery->latest('id')->paginate(10, ['*'], 'inspections_page')->withQueryString();
        $inspectionResults = ['passed', 'failed', 'conditional_pass'];
        $inspectionStatuses = ['scheduled', 'completed', 'overdue'];

        // 4. Spare Parts & Components Inventory for Parts Selector
        $invAttachments = \App\Models\Attachment::where('quantity', '>', 0)->orderBy('name')->get()->map(function ($att) {
            return [
                'inventory_key'  => 'attachment:' . $att->id,
                'inventory_type' => 'attachment',
                'inventory_id'   => $att->id,
                'name'           => $att->name,
                'stock'          => $att->quantity,
                'unit'           => 'pcs',
                'unit_cost'      => (float) $att->unit_cost,
            ];
        });

        $invAccessories = \App\Models\AssetAccessory::where('quantity', '>', 0)->orderBy('accessory_name')->get()->map(function ($acc) {
            return [
                'inventory_key'  => 'accessory:' . $acc->id,
                'inventory_type' => 'accessory',
                'inventory_id'   => $acc->id,
                'name'           => $acc->accessory_name,
                'stock'          => $acc->quantity,
                'unit'           => 'pcs',
                'unit_cost'      => (float) $acc->unit_cost,
            ];
        });

        $inventoryParts = $invAttachments->concat($invAccessories)->values();

        return view('asset-operations.maintenance.index', compact(
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
            'inspectionStatuses',
            'recordType',
            'inventoryParts'
        ));
    }

    /**
     * Authorize that the user has permission to modify maintenance records.
     */
    protected function authorizeEditor(Request $request): void
    {
        $user = $request->user();
        if (!$user || $user->isViewer() || $user->hasRole('viewer', 'staff')) {
            abort(403, 'Unauthorized. Maintenance and work order management is restricted for staff members.');
        }
    }

    /**
     * Show form to create a maintenance work order (restricted for staff).
     */
    public function create(Request $request)
    {
        $this->authorizeEditor($request);
        return redirect()->route('maintenance.index', ['tab' => 'logs']);
    }

    /**
     * Display the specified maintenance record details (accessible for staff).
     */
    public function show(Request $request, Maintenance $maintenance)
    {
        $maintenance->load([
            'asset.category',
            'asset.type',
            'assignedUser',
            'assignedPersonnel',
            'confirmedByUser',
            'verifiedByUser',
            'inspection',
            'parts',
            'receipts',
        ]);

        if ($request->wantsJson()) {
            return response()->json($maintenance);
        }

        return redirect()->route('maintenance.index', ['tab' => 'logs']);
    }

    /**
     * Show form to edit a maintenance record (restricted for staff).
     */
    public function edit(Request $request, Maintenance $maintenance)
    {
        $this->authorizeEditor($request);
        return redirect()->route('maintenance.index', ['tab' => 'logs']);
    }

    /**
     * Store a newly created maintenance record / work order.
     */
    public function store(Request $request)
    {
        $this->authorizeEditor($request);
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
            if (!empty($input['assigned_personnel']) && is_array($input['assigned_personnel'])) {
                $input['assigned_to'] = $input['assigned_personnel'][0];
            } else {
                $input['assigned_to'] = auth()->id();
            }
        }
        $request->merge($input);

        $rules = array_merge([
            'asset_id'             => 'required|exists:assets,id',
            'inspection_id'        => 'nullable|exists:inspections,id',
            'type'                 => 'required|in:routine,preventive,repair,inspection,overhaul,emergency',
            'priority'             => 'nullable|in:low,medium,high,urgent',
            'technician'           => 'nullable|string|max:255',
            'assigned_to'          => 'nullable|exists:users,id',
            'assigned_personnel'   => 'nullable|array',
            'assigned_personnel.*' => 'exists:users,id',
            'confirm_immediately'  => 'nullable|boolean',
            'scheduled_date'       => 'nullable|date',
            'started_at'           => 'nullable|date',
            'estimated_duration'   => 'nullable|string|max:255',
            'cost'                 => 'nullable|numeric|min:0',
            'parts_used'           => 'nullable|string|max:500',
            'notes'                => 'nullable|string',
            'status'               => 'required|in:scheduled,in_progress,completed,pending',
            'parts'                => 'nullable|array',
            'parts.*.inventory_type' => 'nullable|string|in:attachment,accessory,custom',
            'parts.*.inventory_id'   => 'nullable|integer',
            'parts.*.part_name'      => 'nullable|string|max:255',
            'parts.*.quantity'       => 'nullable|integer|min:1',
            'parts.*.unit'           => 'nullable|string|max:50',
            'parts.*.unit_cost'      => 'nullable|numeric|min:0',
        ], ReceiptService::rules());

        $validated = $request->validate($rules, ReceiptService::messages());

        if (empty($validated['priority'])) {
            $validated['priority'] = 'medium';
        }

        if ($validated['status'] === 'in_progress' && empty($validated['started_at'])) {
            $validated['started_at'] = now();
        }

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
            if (empty($validated['started_at'])) {
                $validated['started_at'] = !empty($validated['scheduled_date']) ? \Carbon\Carbon::parse($validated['scheduled_date']) : now();
            }
        }

        $maintenance = DB::transaction(function () use ($validated, $request) {
            $personnel = $validated['assigned_personnel'] ?? [];
            if (!empty($personnel) && empty($validated['assigned_to'])) {
                $validated['assigned_to'] = $personnel[0];
            }

            if ($request->boolean('confirm_immediately')) {
                $validated['confirmed_at'] = now();
                $validated['confirmed_by'] = auth()->id();
            }

            $partsData = $validated['parts'] ?? [];
            unset($validated['assigned_personnel'], $validated['confirm_immediately'], $validated['parts']);
            
            $created = Maintenance::create($validated);
            ReceiptService::attachFromRequest($created, $request);

            if (!empty($personnel)) {
                $created->assignedPersonnel()->sync($personnel);
            }

            if (!empty($partsData)) {
                $this->syncMaintenanceParts($created, $partsData);
            }

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
        $this->authorizeEditor($request);
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
            'asset_id'             => 'required|exists:assets,id',
            'inspection_id'        => 'nullable|exists:inspections,id',
            'type'                 => 'required|in:routine,preventive,repair,inspection,overhaul,emergency',
            'priority'             => 'nullable|in:low,medium,high,urgent',
            'technician'           => 'nullable|string|max:255',
            'assigned_to'          => 'nullable|exists:users,id',
            'assigned_personnel'   => 'nullable|array',
            'assigned_personnel.*' => 'exists:users,id',
            'scheduled_date'       => 'nullable|date',
            'started_at'           => 'nullable|date',
            'estimated_duration'   => 'nullable|string|max:255',
            'cost'                 => 'nullable|numeric|min:0',
            'parts_used'           => 'nullable|string|max:500',
            'notes'                => 'nullable|string',
            'status'               => 'required|in:scheduled,in_progress,completed,pending',
            'parts'                => 'nullable|array',
            'parts.*.inventory_type' => 'nullable|string|in:attachment,accessory,custom',
            'parts.*.inventory_id'   => 'nullable|integer',
            'parts.*.part_name'      => 'nullable|string|max:255',
            'parts.*.quantity'       => 'nullable|integer|min:1',
            'parts.*.unit'           => 'nullable|string|max:50',
            'parts.*.unit_cost'      => 'nullable|numeric|min:0',
        ]);

        if (empty($validated['priority'])) {
            $validated['priority'] = 'medium';
        }

        if ($validated['status'] === 'in_progress' && empty($maintenance->started_at) && empty($validated['started_at'])) {
            $validated['started_at'] = now();
        }

        if ($validated['status'] === 'completed' && empty($maintenance->completed_at)) {
            $validated['completed_at'] = now();
        }

        DB::transaction(function () use ($validated, $maintenance, $request) {
            $personnel = $validated['assigned_personnel'] ?? null;
            if ($request->has('assigned_personnel')) {
                $maintenance->assignedPersonnel()->sync($personnel ?? []);
                if (!empty($personnel) && empty($validated['assigned_to'])) {
                    $validated['assigned_to'] = $personnel[0];
                }
            }
            unset($validated['assigned_personnel']);

            $partsData = $validated['parts'] ?? null;
            unset($validated['parts']);

            $oldValues = $maintenance->only(['type', 'priority', 'technician', 'assigned_to', 'status', 'cost', 'parts_used']);
            $maintenance->update($validated);

            if ($request->has('parts')) {
                $this->syncMaintenanceParts($maintenance, $partsData ?? []);
            }

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
     * Mark a maintenance work order as completed with operational condition verification.
     */
    public function complete(Request $request, Maintenance $maintenance)
    {
        $this->authorizeEditor($request);
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
        if (isset($input['verification_notes'])) {
            $input['verification_notes'] = strip_tags(trim($input['verification_notes']));
        }
        $request->merge($input);

        $rules = array_merge([
            'cost'               => 'nullable|numeric|min:0',
            'parts_used'         => 'nullable|string|max:500',
            'notes'              => 'nullable|string',
            'final_condition'    => 'nullable|in:excellent,good,fair,poor',
            'final_status'       => 'nullable|in:available,under_maintenance,out_of_service,retired',
            'verified_by'        => 'nullable|exists:users,id',
            'verification_notes' => 'nullable|string|max:1000',
            'parts'              => 'nullable|array',
            'parts.*.inventory_type' => 'nullable|string|in:attachment,accessory,custom',
            'parts.*.inventory_id'   => 'nullable|integer',
            'parts.*.part_name'      => 'nullable|string|max:255',
            'parts.*.quantity'       => 'nullable|integer|min:1',
            'parts.*.unit'           => 'nullable|string|max:50',
            'parts.*.unit_cost'      => 'nullable|numeric|min:0',
        ], ReceiptService::rules());

        $validated = $request->validate($rules, ReceiptService::messages());

        DB::transaction(function () use ($maintenance, $validated, $request) {
            $finalCondition = $validated['final_condition'] ?? 'good';
            $finalStatus = $validated['final_status'] ?? AssetLifecycleService::STATUS_AVAILABLE;
            $verifiedBy = !empty($validated['verified_by']) ? $validated['verified_by'] : auth()->id();
            $verificationNotes = $validated['verification_notes'] ?? null;

            $updateData = [
                'status'             => 'completed',
                'completed_at'       => now(),
                'final_condition'    => $finalCondition,
                'verified_by'        => $verifiedBy,
                'verification_notes' => $verificationNotes,
            ];

            if (empty($maintenance->started_at)) {
                $updateData['started_at'] = $maintenance->scheduled_date ? \Carbon\Carbon::parse($maintenance->scheduled_date) : now();
            }

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

            // Process parts if provided
            if (!empty($validated['parts'])) {
                $this->syncMaintenanceParts($maintenance, $validated['parts']);
            }

            ReceiptService::attachFromRequest($maintenance, $request);

            // Requirements 6 & 8: Verify actual operational condition and set status accordingly
            $asset = $maintenance->asset;
            if ($asset) {
                $asset->condition = $finalCondition;
                $asset->save();

                if ($asset->status !== $finalStatus) {
                    if (AssetLifecycleService::canTransition($asset, $finalStatus)) {
                        AssetLifecycleService::transition(
                            $asset,
                            $finalStatus,
                            "Work Order [{$maintenance->work_order_number}] completed. Condition verified as '{$finalCondition}'. Status set to '" . (AssetLifecycleService::statuses()[$finalStatus] ?? $finalStatus) . "'." . ($verificationNotes ? " Notes: {$verificationNotes}" : "")
                        );
                    } else {
                        $asset->status = $finalStatus;
                        $asset->save();
                    }
                }
            }

            AuditTrailService::log(
                'status_changed',
                $maintenance,
                "Work Order [{$maintenance->work_order_number}] verified and completed. Final Condition: {$finalCondition}, Status: {$finalStatus}, Cost: ₱" . number_format($maintenance->cost ?? 0, 2),
                ['status' => 'in_progress'],
                [
                    'status' => 'completed',
                    'completed_at' => $maintenance->completed_at,
                    'final_condition' => $finalCondition,
                    'final_status' => $finalStatus,
                    'verified_by' => $verifiedBy,
                ]
            );
        });

        return redirect()->route('maintenance.index', ['tab' => 'logs'])
            ->with('success', "Work Order [{$maintenance->work_order_number}] verified and completed successfully!");
    }

    /**
     * Remove the specified maintenance record.
     */
    public function destroy(Request $request, Maintenance $maintenance)
    {
        $this->authorizeEditor($request);
        Gate::authorize('delete', $maintenance);

        DB::transaction(function () use ($maintenance) {
            $asset = $maintenance->asset;
            $status = $maintenance->status;
            $woNumber = $maintenance->work_order_number;

            // Restore parts stock to inventory before deletion
            foreach ($maintenance->parts as $part) {
                $this->adjustInventoryStock($part->inventory_type, $part->inventory_id, $part->quantity, 'restore');
            }

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

    /**
     * Synchronize parts & materials consumed on a work order and update inventory stock.
     */
    protected function syncMaintenanceParts(Maintenance $maintenance, array $partsData): void
    {
        // 1. Restore previous parts stock
        foreach ($maintenance->parts as $oldPart) {
            $this->adjustInventoryStock($oldPart->inventory_type, $oldPart->inventory_id, $oldPart->quantity, 'restore');
            $oldPart->delete();
        }

        $summaryList = [];
        $totalPartsCost = 0;

        // 2. Add new parts
        foreach ($partsData as $item) {
            if (empty($item['part_name']) && empty($item['inventory_id'])) {
                continue;
            }

            $invType = $item['inventory_type'] ?? null;
            $invId = !empty($item['inventory_id']) ? (int) $item['inventory_id'] : null;
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $unit = !empty($item['unit']) ? trim($item['unit']) : 'pcs';
            $unitCost = (float) ($item['unit_cost'] ?? 0);
            $partName = trim($item['part_name'] ?? '');

            if (empty($partName) && $invId) {
                if ($invType === 'attachment') {
                    $inv = \App\Models\Attachment::find($invId);
                    $partName = $inv?->name ?? 'Attachment #' . $invId;
                    if ($unitCost == 0 && $inv) $unitCost = (float) $inv->unit_cost;
                } elseif ($invType === 'accessory') {
                    $inv = \App\Models\AssetAccessory::find($invId);
                    $partName = $inv?->accessory_name ?? 'Accessory #' . $invId;
                    if ($unitCost == 0 && $inv) $unitCost = (float) $inv->unit_cost;
                }
            }

            if (empty($partName)) {
                continue;
            }

            $itemTotalCost = round($qty * $unitCost, 2);
            $totalPartsCost += $itemTotalCost;

            if ($invId && in_array($invType, ['attachment', 'accessory'], true)) {
                $this->adjustInventoryStock($invType, $invId, $qty, 'deduct');
            }

            $maintenance->parts()->create([
                'inventory_type' => $invType,
                'inventory_id'   => $invId,
                'part_name'      => $partName,
                'quantity'       => $qty,
                'unit'           => $unit,
                'unit_cost'      => $unitCost,
                'total_cost'     => $itemTotalCost,
            ]);

            $summaryList[] = "{$qty}x {$partName}" . ($unitCost > 0 ? " (₱" . number_format($itemTotalCost, 2) . ")" : "");
        }

        if (!empty($summaryList)) {
            $maintenance->parts_used = implode(', ', $summaryList);
            if (empty($maintenance->cost) || $maintenance->cost == 0) {
                $maintenance->cost = $totalPartsCost;
            }
            $maintenance->save();
        }
    }

    /**
     * Adjust stock quantity on Attachment or AssetAccessory inventory.
     */
    protected function adjustInventoryStock(?string $type, ?int $id, int $quantity, string $direction): void
    {
        if (!$id || !$type) {
            return;
        }

        if ($type === 'attachment') {
            $attachment = \App\Models\Attachment::find($id);
            if ($attachment) {
                if ($direction === 'deduct') {
                    $attachment->quantity = max(0, $attachment->quantity - $quantity);
                } else {
                    $attachment->quantity += $quantity;
                }
                $attachment->save();
            }
        } elseif ($type === 'accessory') {
            $accessory = \App\Models\AssetAccessory::find($id);
            if ($accessory) {
                if ($direction === 'deduct') {
                    $accessory->quantity = max(0, $accessory->quantity - $quantity);
                } else {
                    $accessory->quantity += $quantity;
                }
                $accessory->status = \App\Services\AssetAccessoryService::deriveStatus($accessory->quantity);
                $accessory->save();
            }
        }
    }

    /**
     * Confirm a maintenance work order and assign/update personnel.
     */
    public function confirmMaintenance(Request $request, Maintenance $maintenance)
    {
        $this->authorizeEditor($request);

        $validated = $request->validate([
            'personnel'   => ['nullable', 'array'],
            'personnel.*' => ['exists:users,id'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($maintenance, $validated, $request) {
            $personnel = $validated['personnel'] ?? [];
            $maintenance->confirmed_at = now();
            $maintenance->confirmed_by = auth()->id();

            if (!empty($personnel)) {
                $maintenance->assigned_to = $personnel[0];
            }
            $maintenance->save();

            if ($request->has('personnel')) {
                $maintenance->assignedPersonnel()->sync($personnel);
            }

            $personnelNames = !empty($personnel)
                ? User::whereIn('id', $personnel)->pluck('name')->implode(', ')
                : 'None';

            $notesText = "Work Order [{$maintenance->work_order_number}] confirmed by " . auth()->user()->name . ". Assigned personnel: " . $personnelNames;
            if (!empty($validated['notes'])) {
                $notesText .= ". Notes: " . $validated['notes'];
            }

            AuditTrailService::log(
                'confirmed',
                $maintenance,
                $notesText,
                ['confirmed_at' => null],
                ['confirmed_at' => $maintenance->confirmed_at, 'confirmed_by' => auth()->id()]
            );
        });

        return redirect()->back()->with('success', "Work Order [{$maintenance->work_order_number}] confirmed and personnel assigned successfully.");
    }

    /**
     * Assign or update personnel assigned to work on the maintenance work order.
     */
    public function assignPersonnel(Request $request, Maintenance $maintenance)
    {
        $this->authorizeEditor($request);

        $validated = $request->validate([
            'personnel'   => ['nullable', 'array'],
            'personnel.*' => ['exists:users,id'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ]);

        $personnel = $validated['personnel'] ?? [];

        DB::transaction(function () use ($maintenance, $personnel, $validated) {
            $maintenance->assignedPersonnel()->sync($personnel);
            if (!empty($personnel)) {
                $maintenance->assigned_to = $personnel[0];
                $maintenance->save();
            }

            $personnelNames = !empty($personnel)
                ? User::whereIn('id', $personnel)->pluck('name')->implode(', ')
                : 'None';

            AuditTrailService::log(
                'personnel_assigned',
                $maintenance,
                "Assigned personnel updated for Work Order [{$maintenance->work_order_number}]: {$personnelNames}" . (!empty($validated['notes']) ? " - Notes: {$validated['notes']}" : "")
            );
        });

        return redirect()->back()->with('success', "Personnel assigned to Work Order [{$maintenance->work_order_number}] updated successfully.");
    }
}
