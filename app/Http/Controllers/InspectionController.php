<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Asset;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        return app(MaintenanceController::class)->index($request->merge(['tab' => 'inspections']));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'inspector' => 'nullable|string|max:255',
            'inspection_date' => 'required|date',
            'result' => 'required|in:passed,failed,conditional_pass',
            'findings' => 'nullable|string',
            'next_due_date' => 'nullable|date',
            'status' => 'required|in:scheduled,completed,overdue',
        ]);

        Inspection::create($validated);

        return redirect()->route('maintenance.index', ['tab' => 'inspections'])
            ->with('success', 'Inspection record created successfully!');
    }

    public function update(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'inspector' => 'nullable|string|max:255',
            'inspection_date' => 'required|date',
            'result' => 'required|in:passed,failed,conditional_pass',
            'findings' => 'nullable|string',
            'next_due_date' => 'nullable|date',
            'status' => 'required|in:scheduled,completed,overdue',
        ]);

        $inspection->update($validated);

        return redirect()->route('maintenance.index', ['tab' => 'inspections'])
            ->with('success', 'Inspection record updated successfully!');
    }

    public function destroy(Inspection $inspection)
    {
        $inspection->delete();

        return redirect()->route('maintenance.index', ['tab' => 'inspections'])
            ->with('success', 'Inspection record deleted successfully!');
    }
}
