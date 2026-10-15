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

        $validated = $request->validate([
            'asset_type_id'    => 'required|exists:asset_types,id',
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

        OperationalRequirement::create($validated);

        return redirect()->route('operational-requirements.index')
            ->with('success', 'Operational requirement created successfully!');
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
            'asset_type_id'    => 'required|exists:asset_types,id',
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

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $operationalRequirement->refill((float) $validated['amount']);

        return redirect()->route('operational-requirements.index')
            ->with('success', "Refilled {$operationalRequirement->requirement_type}. Current balance: {$operationalRequirement->fresh()->current_level} {$operationalRequirement->unit}.");
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
