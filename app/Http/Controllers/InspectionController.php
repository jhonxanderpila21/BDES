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
        $user = $request->user();
        if ($user && ($user->isViewer() || $user->hasRole('viewer', 'staff', 'inventory_officer'))) {
            abort(403, 'Unauthorized. Physical inspection and equipment servicing requires Maintenance Officer or Administrator permissions.');
        }
    }

    /**
     * Show form to create an inspection (restricted for staff).
     */
    public function create(Request $request)
    {
        $this->authorizeInspector($request);
        return redirect()->route('maintenance.index', ['tab' => 'inspections']);
    }

    /**
     * Display the specified inspection details (accessible for staff).
     */
    public function show(Request $request, Inspection $inspection)
    {
        $inspection->load(['asset.category', 'receipts']);

        if ($request->wantsJson()) {
            return response()->json($inspection);
        }

        return redirect()->route('maintenance.index', ['tab' => 'inspections']);
    }

    /**
     * Show form to edit an inspection (restricted for staff).
     */
    public function edit(Request $request, Inspection $inspection)
    {
        $this->authorizeInspector($request);
        return redirect()->route('maintenance.index', ['tab' => 'inspections']);
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
        if (empty($input['status'])) {
            $input['status'] = 'completed';
        }
        $request->merge($input);

        $rules = array_merge([
            'asset_id'        => 'required|exists:assets,id',
            'inspector'       => 'nullable|string|max:255',
            'inspection_date' => 'required|date',
            'result'          => 'required|in:passed,failed,conditional_pass',
            'findings'        => 'nullable|string',
            'next_due_date'   => 'nullable|date|after_or_equal:inspection_date',
            'status'          => 'required|in:scheduled,completed,overdue',
        ], \App\Services\ReceiptService::rules());

        $messages = array_merge([
            'next_due_date.after_or_equal' => 'The next due date must be on or after the current inspection date.',
        ], \App\Services\ReceiptService::messages());

        $validated = $request->validate($rules, $messages);
        unset($validated['receipts'], $validated['receipt_reference_number']);

        $inspection = Inspection::create($validated);
        \App\Services\ReceiptService::attachFromRequest($inspection, $request);

        return redirect()->route('maintenance.index', ['tab' => 'inspections'])
            ->with('success', 'Inspection record created successfully with supporting documents!');
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
