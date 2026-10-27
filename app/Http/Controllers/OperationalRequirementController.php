<?php

namespace App\Http\Controllers;

use App\Models\OperationalRequirement;
use App\Models\AssetType;
use Illuminate\Http\Request;

class OperationalRequirementController extends Controller
{
    public function index(Request $request)
    {
        $query = OperationalRequirement::with('assetType');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('requirement_type', 'like', "%{$search}%");
        }

        if ($request->boolean('low')) {
            $query->whereColumn('current_level', '<=', 'minimum_level');
        }

        $requirements = $query->latest()->paginate(10)->withQueryString();
        $assetTypes = AssetType::orderBy('name')->get();

        return view('admin.operational-requirements.index', compact('requirements', 'assetTypes'));
    }

    protected function authorizeEditor(Request $request): void
    {
        if ($request->user() && $request->user()->hasRole('viewer')) {
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

        $requirement = OperationalRequirement::create($validated);
        \App\Services\ReceiptService::attachFromRequest($requirement, $request);

        return redirect()->route('operational-requirements.index')
            ->with('success', 'Operational requirement created successfully with supporting documents!');
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
