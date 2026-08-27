<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaintenanceScheduleRequest;
use App\Http\Requests\UpdateMaintenanceScheduleRequest;
use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Services\MaintenanceScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MaintenanceScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Dynamically update status before rendering to ensure accuracy
        // In a real prod environment, this might be handled completely by a cron job
        $allSchedules = MaintenanceSchedule::all();
        foreach ($allSchedules as $schedule) {
            $schedule->refreshStatus();
        }

        $query = MaintenanceSchedule::with('asset');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('maintenance_task', 'like', "%{$search}%")
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

        if ($request->filled('interval_type')) {
            $query->where('interval_type', $request->interval_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->latest()->paginate(10)->withQueryString();

        $assetTypes = MaintenanceSchedule::select('asset_type')->distinct()->orderBy('asset_type')->pluck('asset_type');
        $intervalTypes = MaintenanceScheduleService::intervalTypes();
        $statuses = MaintenanceScheduleService::statuses();
        $assets = Asset::with('type')->orderBy('name')->get();

        return view('admin.maintenance-schedules.index', compact(
            'schedules',
            'assetTypes',
            'intervalTypes',
            'statuses',
            'assets'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMaintenanceScheduleRequest $request)
    {
        $validated = $request->validated();
        
        $asset = Asset::with('type')->findOrFail($validated['asset_id']);
        $validated['asset_type'] = $asset->type->name ?? 'Unknown';

        MaintenanceSchedule::create($validated);

        return redirect()->route('maintenance-schedules.index')
            ->with('success', 'Preventive maintenance schedule created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMaintenanceScheduleRequest $request, MaintenanceSchedule $maintenanceSchedule)
    {
        $validated = $request->validated();

        $asset = Asset::with('type')->findOrFail($validated['asset_id']);
        $validated['asset_type'] = $asset->type->name ?? 'Unknown';

        $maintenanceSchedule->update($validated);

        return redirect()->route('maintenance-schedules.index')
            ->with('success', 'Preventive maintenance schedule updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaintenanceSchedule $maintenanceSchedule)
    {
        $maintenanceSchedule->delete();

        return redirect()->route('maintenance-schedules.index')
            ->with('success', 'Preventive maintenance schedule deleted successfully!');
    }

    /**
     * Mark the maintenance schedule as completed.
     */
    public function complete(Request $request, MaintenanceSchedule $maintenanceSchedule)
    {
        $validated = $request->validate([
            'completion_date' => 'required|date|before_or_equal:today',
            'technician' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $maintenanceSchedule->markAsCompleted(
            Carbon::parse($validated['completion_date']),
            $validated['technician'] ?? null,
            $validated['notes'] ?? null
        );

        return redirect()->route('maintenance-schedules.index')
            ->with('success', "Maintenance task '{$maintenanceSchedule->maintenance_task}' marked as completed.");
    }
}
