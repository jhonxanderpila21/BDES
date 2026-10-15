<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetReturn;
use App\Models\Deployment;
use App\Models\Location;
use App\Models\User;
use App\Services\AssetLifecycleService;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class DeploymentController extends Controller
{
    /**
     * Display a listing of deployments and returns.
     */
    public function index(Request $request)
    {
        $query = Deployment::with(['asset.category', 'returnRecord.receivedByUser', 'approvedByUser', 'user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('destination', 'like', "%{$search}%")
                    ->orWhere('assigned_team', 'like', "%{$search}%")
                    ->orWhere('responsible_person', 'like', "%{$search}%")
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
        $users = User::orderBy('name')->get();

        return view('admin.deployment.index', compact(
            'deployments',
            'statusCounts',
            'availableAssets',
            'locations',
            'users'
        ));
    }

    /**
     * Store a newly created deployment.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Deployment::class);

        // Normalize text inputs
        $input = $request->all();
        if (isset($input['destination'])) {
            $input['destination'] = strip_tags(trim($input['destination']));
        }
        if (isset($input['assigned_team'])) {
            $input['assigned_team'] = strip_tags(trim($input['assigned_team']));
        }
        if (isset($input['responsible_person'])) {
            $input['responsible_person'] = strip_tags(trim($input['responsible_person']));
        }
        if (isset($input['purpose'])) {
            $input['purpose'] = strip_tags(trim($input['purpose']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'asset_id'             => [
                'required',
                Rule::exists('assets', 'id')->where('status', 'available'),
            ],
            'destination'          => ['required', 'string', 'max:255'],
            'assigned_team'        => ['nullable', 'string', 'max:255'],
            'responsible_person'   => ['nullable', 'string', 'max:255'],
            'condition_before'     => ['nullable', 'in:excellent,good,fair,poor'],
            'approved_by'          => ['nullable', 'exists:users,id'],
            'deployment_date'      => ['required', 'date'],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:deployment_date'],
            'purpose'              => ['nullable', 'string'],
        ], [
            'asset_id.exists' => 'The selected asset is either not found or already deployed/unavailable.',
            'expected_return_date.after_or_equal' => 'The expected return date must be on or after the deployment date.',
        ]);

        $validated['status'] = 'active';
        if (empty($validated['approved_by'])) {
            $validated['approved_by'] = auth()->id();
        }
        if (empty($validated['responsible_person'])) {
            $validated['responsible_person'] = !empty($validated['assigned_team']) ? $validated['assigned_team'] : (auth()->user()?->name ?? 'Field Response Team');
        }

        DB::transaction(function () use ($validated) {
            $asset = Asset::find($validated['asset_id']);
            if (empty($validated['condition_before']) && $asset) {
                $validated['condition_before'] = $asset->condition;
            }

            $deployment = Deployment::create($validated);

            if ($asset) {
                if (AssetLifecycleService::canTransition($asset, AssetLifecycleService::STATUS_DEPLOYED)) {
                    AssetLifecycleService::transition(
                        $asset,
                        AssetLifecycleService::STATUS_DEPLOYED,
                        "Dispatched to [{$deployment->destination}] for mission: " . ($deployment->purpose ?? 'Emergency response')
                    );
                } else {
                    $asset->update(['status' => 'deployed']);
                }
            }

            AuditTrailService::log(
                'created',
                $deployment,
                "Asset [{$asset?->code}] deployed to [{$deployment->destination}]. Assigned custodian: {$deployment->responsible_person}.",
                null,
                $deployment->only(['asset_id', 'destination', 'responsible_person', 'deployment_date', 'expected_return_date'])
            );
        });

        return redirect()->route('deployment.index')
            ->with('success', 'Asset deployment created successfully!');
    }

    /**
     * Record a return for an existing deployment.
     */
    public function returnAsset(Request $request, Deployment $deployment)
    {
        Gate::authorize('return', $deployment);

        // Normalize text inputs
        $input = $request->all();
        if (isset($input['returned_by'])) {
            $input['returned_by'] = strip_tags(trim($input['returned_by']));
        }
        if (isset($input['damage_notes'])) {
            $input['damage_notes'] = strip_tags(trim($input['damage_notes']));
        }
        if (isset($input['missing_accessories'])) {
            $input['missing_accessories'] = strip_tags(trim($input['missing_accessories']));
        }
        if (isset($input['actual_location'])) {
            $input['actual_location'] = strip_tags(trim($input['actual_location']));
        }
        $request->merge($input);

        $depDate = $deployment->deployment_date ? \Carbon\Carbon::parse($deployment->deployment_date)->toDateString() : null;

        $validated = $request->validate([
            'return_date'         => array_merge(['required', 'date'], $depDate ? ['after_or_equal:' . $depDate] : []),
            'returned_by'         => ['nullable', 'string', 'max:255'],
            'received_by'         => ['nullable', 'exists:users,id'],
            'condition'           => ['required', 'in:excellent,good,fair,poor'],
            'damage_notes'        => ['nullable', 'string'],
            'missing_accessories' => ['nullable', 'string'],
            'actual_location'     => ['nullable', 'string', 'max:255'],
            'status'              => ['required', 'in:received,damage_noted,incomplete_accessories'],
        ], [
            'return_date.after_or_equal' => "The return date cannot precede the deployment date ({$depDate}).",
        ]);

        if (empty($validated['received_by'])) {
            $validated['received_by'] = auth()->id();
        }
        if (empty($validated['returned_by'])) {
            $validated['returned_by'] = $deployment->responsible_person ?? ($deployment->assigned_team ?? auth()->user()?->name);
        }

        DB::transaction(function () use ($validated, $deployment) {
            // Create or update return record linked to deployment
            $returnRecord = $deployment->returnRecord()->updateOrCreate(
                ['deployment_id' => $deployment->id],
                [
                    'asset_id'            => $deployment->asset_id,
                    'returned_by'         => $validated['returned_by'] ?? $deployment->responsible_person ?? $deployment->assigned_team,
                    'received_by'         => $validated['received_by'],
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

                AuditTrailService::log(
                    'status_changed',
                    $asset,
                    "Asset returned from deployment to [{$deployment->destination}]. Post-operation condition: [{$validated['condition']}]. Returned to: " . ($validated['actual_location'] ?? 'Station'),
                    ['status' => 'deployed'],
                    ['status' => 'available', 'condition' => $validated['condition']]
                );
            }

            AuditTrailService::log(
                'created',
                $returnRecord,
                "Return custody verified for deployment #{$deployment->id}. Condition: {$validated['condition']}.",
                null,
                $returnRecord->toArray()
            );
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
            'responsible_person'   => ['nullable', 'string', 'max:255'],
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

            AuditTrailService::log('updated', $deployment, "Deployment #{$deployment->id} details updated.");
        });

        return redirect()->route('deployment.index')
            ->with('success', 'Deployment record updated successfully!');
    }

    /**
     * Remove the specified deployment.
     */
    public function destroy(Deployment $deployment)
    {
        Gate::authorize('delete', $deployment);

        DB::transaction(function () use ($deployment) {
            $asset = $deployment->asset;

            // If deleted deployment was active, release the asset back to available
            if ($deployment->status !== 'returned' && $asset && $asset->status === 'deployed') {
                $asset->update(['status' => 'available']);
            }

            AuditTrailService::log('deleted', $deployment, "Deployment record #{$deployment->id} removed.");
            $deployment->delete();
        });

        return redirect()->route('deployment.index')
            ->with('success', 'Deployment record deleted successfully!');
    }
}
