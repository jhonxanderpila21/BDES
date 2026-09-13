<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Services\MaintenanceScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'active_maintenance' => Maintenance::whereIn('status', ['in_progress', 'scheduled'])->count(),
            'total_inspections'  => Inspection::count(),
        ];

        // Assets catalogue for modals
        $assets = Asset::with(['type', 'category'])->orderBy('name')->get();

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
            $schedulesQuery->where('status', $request->status);
        }
        $schedules = $schedulesQuery->latest('id')->paginate(10, ['*'], 'schedules_page')->withQueryString();
        $assetTypes = MaintenanceSchedule::select('asset_type')->distinct()->orderBy('asset_type')->pluck('asset_type');
        $intervalTypes = MaintenanceScheduleService::intervalTypes();
        $scheduleStatuses = MaintenanceScheduleService::statuses();

        // 2. Maintenance Logs & Repairs Tab Data
        $maintenancesQuery = Maintenance::with(['asset.category']);
        if ($request->filled('search') && $activeTab === 'logs') {
            $search = $request->search;
            $maintenancesQuery->where(function ($q) use ($search) {
                $q->where('technician', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('type')) {
            $maintenancesQuery->where('type', $request->type);
        }
        if ($request->filled('status') && $activeTab === 'logs') {
            $maintenancesQuery->where('status', $request->status);
        }
        $maintenances = $maintenancesQuery->latest('id')->paginate(10, ['*'], 'logs_page')->withQueryString();
        $maintenanceTypes = ['routine', 'preventive', 'repair', 'inspection'];
        $maintenanceStatuses = ['scheduled', 'in_progress', 'completed', 'pending'];

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
            'schedules',
            'assetTypes',
            'intervalTypes',
            'scheduleStatuses',
            'maintenances',
            'maintenanceTypes',
            'maintenanceStatuses',
            'inspections',
            'inspectionResults',
            'inspectionStatuses'
        ));
    }

    /**
     * Store a newly created maintenance record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id'           => 'required|exists:assets,id',
            'type'               => 'required|in:routine,preventive,repair,inspection',
            'technician'         => 'nullable|string|max:255',
            'scheduled_date'     => 'nullable|date',
            'estimated_duration' => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
            'status'             => 'required|in:scheduled,in_progress,completed,pending',
        ]);

        DB::transaction(function () use ($validated) {
            Maintenance::create($validated);

            if ($validated['status'] === 'in_progress') {
                $asset = Asset::find($validated['asset_id']);
                if ($asset) {
                    $asset->update(['status' => 'under_maintenance']);
                }
            }
        });

        return redirect()->route('maintenance.index', ['tab' => 'logs'])
            ->with('success', 'Maintenance record created successfully!');
    }

    /**
     * Update the specified maintenance record.
     */
    public function update(Request $request, Maintenance $maintenance)
    {
        $validated = $request->validate([
            'asset_id'           => 'required|exists:assets,id',
            'type'               => 'required|in:routine,preventive,repair,inspection',
            'technician'         => 'nullable|string|max:255',
            'scheduled_date'     => 'nullable|date',
            'estimated_duration' => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
            'status'             => 'required|in:scheduled,in_progress,completed,pending',
        ]);

        DB::transaction(function () use ($validated, $maintenance) {
            $maintenance->update($validated);

            $asset = Asset::find($validated['asset_id']);
            if ($asset) {
                if ($validated['status'] === 'in_progress') {
                    $asset->update(['status' => 'under_maintenance']);
                } elseif ($validated['status'] === 'completed' && $asset->status === 'under_maintenance') {
                    $asset->update(['status' => 'available']);
                }
            }
        });

        return redirect()->route('maintenance.index', ['tab' => 'logs'])
            ->with('success', 'Maintenance record updated successfully!');
    }

    /**
     * Mark a maintenance work order as completed.
     */
    public function complete(Request $request, Maintenance $maintenance)
    {
        DB::transaction(function () use ($maintenance) {
            $maintenance->update(['status' => 'completed']);

            $asset = $maintenance->asset;
            if ($asset && $asset->status === 'under_maintenance') {
                $asset->update(['status' => 'available']);
            }
        });

        return redirect()->route('maintenance.index', ['tab' => 'logs'])
            ->with('success', 'Maintenance task marked as completed!');
    }

    /**
     * Remove the specified maintenance record.
     */
    public function destroy(Maintenance $maintenance)
    {
        DB::transaction(function () use ($maintenance) {
            $asset = $maintenance->asset;
            $status = $maintenance->status;
            $maintenance->delete();

            if ($status === 'in_progress' && $asset && $asset->status === 'under_maintenance') {
                $hasOther = Maintenance::where('asset_id', $asset->id)->where('status', 'in_progress')->exists();
                if (!$hasOther) {
                    $asset->update(['status' => 'available']);
                }
            }
        });

        return redirect()->route('maintenance.index', ['tab' => 'logs'])
            ->with('success', 'Maintenance record deleted successfully!');
    }
}
