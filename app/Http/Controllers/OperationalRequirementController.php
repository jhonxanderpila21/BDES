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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_type_id' => 'required|exists:asset_types,id',
            'requirement_type' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'current_level' => 'required|numeric|min:0',
            'maximum_capacity' => 'required|numeric|min:0',
            'minimum_level' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        OperationalRequirement::create($validated);

        return redirect()->route('operational-requirements.index')
            ->with('success', 'Operational requirement created successfully!');
    }

    public function update(Request $request, OperationalRequirement $operationalRequirement)
    {
        $validated = $request->validate([
            'asset_type_id' => 'required|exists:asset_types,id',
            'requirement_type' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'current_level' => 'required|numeric|min:0',
            'maximum_capacity' => 'required|numeric|min:0',
            'minimum_level' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $operationalRequirement->update($validated);

        return redirect()->route('operational-requirements.index')
            ->with('success', 'Operational requirement updated successfully!');
    }

    public function destroy(OperationalRequirement $operationalRequirement)
    {
        $operationalRequirement->delete();

        return redirect()->route('operational-requirements.index')
            ->with('success', 'Operational requirement deleted successfully!');
    }

    public function refill(Request $request, OperationalRequirement $operationalRequirement)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $operationalRequirement->refill($validated['amount']);

        return redirect()->route('operational-requirements.index')
            ->with('success', "Refilled {$operationalRequirement->requirement_type}.");
    }

    public function consume(Request $request, OperationalRequirement $operationalRequirement)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $operationalRequirement->consume($validated['amount']);

        return redirect()->route('operational-requirements.index')
            ->with('success', "Consumed from {$operationalRequirement->requirement_type}.");
    }
}
