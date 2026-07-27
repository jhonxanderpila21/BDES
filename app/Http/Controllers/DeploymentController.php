<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Asset;
use Illuminate\Http\Request;

class DeploymentController extends Controller
{
    /**
     * Display a listing of deployments.
     */
    public function index()
    {
        $deployments = Deployment::with('asset')->paginate(10);
        return view('admin.deployment.index', ['deployments' => $deployments]);
    }

    /**
     * Store a newly created deployment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'destination' => 'required|string',
            'assigned_team' => 'nullable|string',
            'deployment_date' => 'required|date',
            'expected_return_date' => 'nullable|date',
            'purpose' => 'nullable|string',
            'status' => 'in:active,pending_return,returned',
        ]);

        Deployment::create($validated);

        // Update asset status
        $asset = Asset::find($request->asset_id);
        $asset->update(['status' => 'deployed']);

        return redirect()->route('deployment.index')
            ->with('success', 'Deployment created successfully!');
    }

    /**
     * Update the specified deployment in storage.
     */
    public function update(Request $request, Deployment $deployment)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'destination' => 'required|string',
            'assigned_team' => 'nullable|string',
            'deployment_date' => 'required|date',
            'expected_return_date' => 'nullable|date',
            'purpose' => 'nullable|string',
            'status' => 'in:active,pending_return,returned',
        ]);

        // Update asset status based on deployment status
        if ($validated['status'] === 'returned') {
            Asset::find($request->asset_id)->update(['status' => 'available']);
        }

        $deployment->update($validated);

        return redirect()->route('deployment.index')
            ->with('success', 'Deployment updated successfully!');
    }

    /**
     * Remove the specified deployment from storage.
     */
    public function destroy(Deployment $deployment)
    {
        $deployment->delete();

        return redirect()->route('deployment.index')
            ->with('success', 'Deployment deleted successfully!');
    }
}
