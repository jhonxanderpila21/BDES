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
        return app(MaintenanceController::class)->index($request->merge(['tab' => 'schedules']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMaintenanceScheduleRequest $request)
    {
        $validated = $request->validated();
        
        $asset = Asset::with('type')->findOrFail($validated['asset_id']);
        $validated['asset_type'] = $asset->type->name ?? 'Unknown';

        unset($validated['receipts'], $validated['receipt_reference_number']);
        $schedule = MaintenanceSchedule::create($validated);
        \App\Services\ReceiptService::attachFromRequest($schedule, $request);

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
     * Authorize schedule modifications.
     */
    protected function authorizeEditor(Request $request): void
    {
        $user = $request->user();
        if (!$user || $user->isViewer() || $user->hasRole('viewer', 'staff') || (!$user->isAdmin() && !$user->hasRole('maintenance_officer', 'bdrrmc_officer'))) {
            abort(403, 'Unauthorized. Schedule management requires Maintenance Officer or Administrator permissions.');
        }
    }

    /**
     * Display the specified maintenance schedule details (accessible for staff).
     */
    public function show(Request $request, MaintenanceSchedule $maintenanceSchedule)
    {
        $maintenanceSchedule->load(['asset.type', 'asset.category', 'receipts']);

        if ($request->wantsJson()) {
            return response()->json($maintenanceSchedule);
        }

        return redirect()->route('maintenance-schedules.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, MaintenanceSchedule $maintenanceSchedule)
    {
        $this->authorizeEditor($request);

        $maintenanceSchedule->delete();

        return redirect()->route('maintenance-schedules.index')
            ->with('success', 'Preventive maintenance schedule deleted successfully!');
    }

    /**
     * Mark the maintenance schedule as completed.
     */
    public function complete(Request $request, MaintenanceSchedule $maintenanceSchedule)
    {
        $this->authorizeEditor($request);

        $input = $request->all();
        if (isset($input['technician'])) {
            $input['technician'] = strip_tags(trim($input['technician']));
        }
        if (empty($input['technician'])) {
            $input['technician'] = auth()->user()?->name;
        }
        if (isset($input['notes'])) {
            $input['notes'] = strip_tags(trim($input['notes']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'completion_date' => 'required|date|before_or_equal:today',
            'technician' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ], [
            'completion_date.before_or_equal' => 'The completion date cannot be set in the future.',
        ]);

        $maintenanceSchedule->markAsCompleted(
            Carbon::parse($validated['completion_date']),
            $validated['technician'] ?? auth()->user()?->name,
            $validated['notes'] ?? null
        );

        return redirect()->route('maintenance-schedules.index')
            ->with('success', "Maintenance task '{$maintenanceSchedule->maintenance_task}' marked as completed.");
    }
}
