<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetReturn;
use App\Models\Deployment;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DeploymentController extends Controller
{
    /**
     * Display a listing of deployments and returns.
     */
    public function index(Request $request)
    {
        $query = Deployment::with(['asset.category', 'returnRecord']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('destination', 'like', "%{$search}%")
                    ->orWhere('assigned_team', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('asset', function ($assetQuery) use ($search) {
                        $assetQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('returnRecord', function ($returnQuery) use ($search) {
                        $returnQuery->where('returned_by', 'like', "%{$search}%")
                            ->orWhere('actual_location', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by Status
        if ($request->filled('status') && in_array($request->status, ['active', 'pending_return', 'returned'])) {
            $query->where('status', $request->status);
        }

        $deployments = $query->latest('id')->paginate(10)->withQueryString();

        // Status counts for summary metric cards
        $statusCounts = [
            'total'          => Deployment::count(),
            'active'         => Deployment::where('status', 'active')->count(),
            'pending_return' => Deployment::where('status', 'pending_return')->count(),
            'returned'       => Deployment::where('status', 'returned')->count(),
        ];

        // Available assets that can be deployed
        $availableAssets = Asset::with('category')
            ->where('status', 'available')
            ->orderBy('name')
            ->get();

        $locations = Location::orderBy('name')->get();

        return view('admin.deployment.index', compact(
            'deployments',
            'statusCounts',
            'availableAssets',
            'locations'
        ));
    }

    /**
     * Store a newly created deployment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id'             => [
                'required',
                Rule::exists('assets', 'id')->where('status', 'available'),
            ],
            'destination'          => ['required', 'string', 'max:255'],
            'assigned_team'        => ['nullable', 'string', 'max:255'],
            'deployment_date'      => ['required', 'date'],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:deployment_date'],
            'purpose'              => ['nullable', 'string'],
        ], [
            'asset_id.exists' => 'The selected asset is either not found or already deployed/unavailable.',
        ]);

        $validated['status'] = 'active';

        DB::transaction(function () use ($validated) {
            Deployment::create($validated);

            $asset = Asset::find($validated['asset_id']);
            if ($asset) {
                $asset->update(['status' => 'deployed']);
            }
        });

        return redirect()->route('deployment.index')
            ->with('success', 'Asset deployment created successfully!');
    }

    /**
     * Record a return for an existing deployment.
     */
    public function returnAsset(Request $request, Deployment $deployment)
    {
        $validated = $request->validate([
            'return_date'         => ['required', 'date'],
            'returned_by'         => ['nullable', 'string', 'max:255'],
            'condition'           => ['required', 'in:excellent,good,fair,poor'],
            'damage_notes'        => ['nullable', 'string'],
            'missing_accessories' => ['nullable', 'string'],
            'actual_location'     => ['nullable', 'string', 'max:255'],
            'status'              => ['required', 'in:received,damage_noted,incomplete_accessories'],
        ]);

        DB::transaction(function () use ($validated, $deployment) {
            // Create or update return record linked to deployment
            $deployment->returnRecord()->updateOrCreate(
                ['deployment_id' => $deployment->id],
                [
                    'asset_id'            => $deployment->asset_id,
                    'returned_by'         => $validated['returned_by'] ?? $deployment->assigned_team,
                    'return_date'         => $validated['return_date'],
                    'condition'           => $validated['condition'],
                    'damage_notes'        => $validated['damage_notes'] ?? null,
                    'missing_accessories' => $validated['missing_accessories'] ?? null,
                    'actual_location'     => $validated['actual_location'] ?? null,
                    'status'              => $validated['status'],
                ]
            );

            // Transition deployment status to returned
            $deployment->update(['status' => 'returned']);

            // Update asset status and condition
            $asset = $deployment->asset;
            if ($asset) {
                $asset->status = 'available';
                $asset->condition = $validated['condition'];

                // Sync location if matched
                if (!empty($validated['actual_location'])) {
                    $matchedLocation = Location::where('name', $validated['actual_location'])->first();
                    if ($matchedLocation) {
                        $asset->location_id = $matchedLocation->id;
                    }
                }

                $asset->save();
            }
        });

        return redirect()->route('deployment.index')
            ->with('success', 'Asset return recorded successfully and inventory status updated!');
    }

    /**
     * Update the specified deployment.
     */
    public function update(Request $request, Deployment $deployment)
    {
        $validated = $request->validate([
            'destination'          => ['required', 'string', 'max:255'],
            'assigned_team'        => ['nullable', 'string', 'max:255'],
            'deployment_date'      => ['required', 'date'],
            'expected_return_date' => ['nullable', 'date'],
            'purpose'              => ['nullable', 'string'],
            'status'               => ['required', 'in:active,pending_return,returned'],
        ]);

        DB::transaction(function () use ($validated, $deployment) {
            $oldStatus = $deployment->status;
            $deployment->update($validated);

            $asset = $deployment->asset;
            if ($asset) {
                if ($validated['status'] === 'returned' && $oldStatus !== 'returned') {
                    $asset->update(['status' => 'available']);
                } elseif ($validated['status'] !== 'returned' && $oldStatus === 'returned') {
                    $asset->update(['status' => 'deployed']);
                }
            }
        });

        return redirect()->route('deployment.index')
            ->with('success', 'Deployment record updated successfully!');
    }

    /**
     * Remove the specified deployment.
     */
    public function destroy(Deployment $deployment)
    {
        DB::transaction(function () use ($deployment) {
            $asset = $deployment->asset;

            // If deleted deployment was active, release the asset back to available
            if ($deployment->status !== 'returned' && $asset && $asset->status === 'deployed') {
                $asset->update(['status' => 'available']);
            }

            $deployment->delete();
        });

        return redirect()->route('deployment.index')
            ->with('success', 'Deployment record deleted successfully!');
    }
}
