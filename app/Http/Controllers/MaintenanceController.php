<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Asset;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Maintenance::with('asset');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('asset', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $maintenances = $query->latest()->paginate(10)->withQueryString();
        $assets = Asset::orderBy('name')->get();

        return view('admin.maintenance.index', compact('maintenances', 'assets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:routine,preventive,repair,inspection',
            'technician' => 'nullable|string|max:255',
            'scheduled_date' => 'nullable|date',
            'estimated_duration' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,in_progress,completed,pending',
        ]);

        Maintenance::create($validated);

        // If maintenance is in progress, update asset status
        if ($validated['status'] === 'in_progress') {
            $asset = Asset::find($validated['asset_id']);
            $asset->status = 'under_maintenance';
            $asset->save();
        }

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance record created successfully!');
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:routine,preventive,repair,inspection',
            'technician' => 'nullable|string|max:255',
            'scheduled_date' => 'nullable|date',
            'estimated_duration' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,in_progress,completed,pending',
        ]);

        $maintenance->update($validated);

        // Sync asset status based on maintenance status
        $asset = Asset::find($validated['asset_id']);
        if ($validated['status'] === 'in_progress') {
            $asset->status = 'under_maintenance';
            $asset->save();
        } elseif ($validated['status'] === 'completed' && $asset->status === 'under_maintenance') {
            $asset->status = 'available';
            $asset->save();
        }

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance record updated successfully!');
    }

    public function destroy(Maintenance $maintenance)
    {
        $maintenance->delete();

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance record deleted successfully!');
    }
}
