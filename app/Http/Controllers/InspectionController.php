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

    protected function authorizeInspector(Request $request): void
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
            abort(403, 'Unauthorized. Inspection recording requires operational permissions.');
        }
    }

    public function store(Request $request)
    {
        $this->authorizeInspector($request);

        $input = $request->all();
        if (isset($input['inspector'])) {
            $input['inspector'] = strip_tags(trim($input['inspector']));
        }
        if (empty($input['inspector'])) {
            $input['inspector'] = auth()->user()?->name;
        }
        if (isset($input['findings'])) {
            $input['findings'] = strip_tags(trim($input['findings']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'asset_id'        => 'required|exists:assets,id',
            'inspector'       => 'nullable|string|max:255',
            'inspection_date' => 'required|date',
            'result'          => 'required|in:passed,failed,conditional_pass',
            'findings'        => 'nullable|string',
            'next_due_date'   => 'nullable|date|after_or_equal:inspection_date',
            'status'          => 'required|in:scheduled,completed,overdue',
        ], [
            'next_due_date.after_or_equal' => 'The next due date must be on or after the current inspection date.',
        ]);

        Inspection::create($validated);

        return redirect()->route('maintenance.index', ['tab' => 'inspections'])
            ->with('success', 'Inspection record created successfully!');
    }

    public function update(Request $request, Inspection $inspection)
    {
        $this->authorizeInspector($request);

        $input = $request->all();
        if (isset($input['inspector'])) {
            $input['inspector'] = strip_tags(trim($input['inspector']));
        }
        if (empty($input['inspector'])) {
            $input['inspector'] = auth()->user()?->name;
        }
        if (isset($input['findings'])) {
            $input['findings'] = strip_tags(trim($input['findings']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'asset_id'        => 'required|exists:assets,id',
            'inspector'       => 'nullable|string|max:255',
            'inspection_date' => 'required|date',
            'result'          => 'required|in:passed,failed,conditional_pass',
            'findings'        => 'nullable|string',
            'next_due_date'   => 'nullable|date|after_or_equal:inspection_date',
            'status'          => 'required|in:scheduled,completed,overdue',
        ], [
            'next_due_date.after_or_equal' => 'The next due date must be on or after the current inspection date.',
        ]);

        $inspection->update($validated);

        return redirect()->route('maintenance.index', ['tab' => 'inspections'])
            ->with('success', 'Inspection record updated successfully!');
    }

    public function destroy(Request $request, Inspection $inspection)
    {
        $this->authorizeInspector($request);

        $inspection->delete();

        return redirect()->route('maintenance.index', ['tab' => 'inspections'])
            ->with('success', 'Inspection record deleted successfully!');
    }
}
