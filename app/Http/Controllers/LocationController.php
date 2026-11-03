<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Location;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    /**
     * Display a listing of locations.
     */
    public function index(Request $request)
    {
        $query = Location::with([
            'assets' => function ($q) {
                $q->with(['category', 'type'])->orderBy('name');
            }
        ])->withCount([
            'assets',
            'assets as available_assets_count' => function ($q) {
                $q->where('status', 'available');
            }
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $locations = $query->orderBy('name')->paginate(10)->withQueryString();

        $stats = [
            'total_locations'    => Location::count(),
            'total_assets'       => \App\Models\Asset::whereNotNull('location_id')->count(),
            'available_assets'   => \App\Models\Asset::whereNotNull('location_id')->where('status', 'available')->count(),
            'total_capacity'     => Location::sum('storage_capacity'),
        ];

        $allLocations = Location::withCount('assets')->orderBy('name')->get(['id', 'name', 'type', 'storage_capacity']);

        return view('tracking-and-reporting.locations.index', [
            'locations'    => $locations,
            'allLocations' => $allLocations,
            'stats'        => $stats,
        ]);
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $input = $request->all();
        if (isset($input['name'])) {
            $input['name'] = strip_tags(trim($input['name']));
        }
        if (isset($input['type'])) {
            $input['type'] = strip_tags(trim($input['type']));
        }
        if (isset($input['address'])) {
            $input['address'] = strip_tags(trim($input['address']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'name'             => 'required|string|max:255|unique:locations,name',
            'type'             => 'nullable|string|max:100',
            'address'          => 'nullable|string|max:255',
            'storage_capacity' => 'nullable|integer|min:0',
        ]);

        $created = Location::create($validated);

        if ($request->input('return_to') === 'assets') {
            return redirect()->route('assets.index', [
                'tab'                  => 'assets',
                'reopen_add_asset'     => 1,
                'selected_location_id' => $created->id,
            ])->with('success', 'Location created successfully!');
        }

        return redirect()->route('locations.index')
            ->with('success', 'Location created successfully!');
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, Location $location)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $input = $request->all();
        if (isset($input['name'])) {
            $input['name'] = strip_tags(trim($input['name']));
        }
        if (isset($input['type'])) {
            $input['type'] = strip_tags(trim($input['type']));
        }
        if (isset($input['address'])) {
            $input['address'] = strip_tags(trim($input['address']));
        }
        $request->merge($input);

        $validated = $request->validate([
            'name'             => 'required|string|max:255|unique:locations,name,' . $location->id,
            'type'             => 'nullable|string|max:100',
            'address'          => 'nullable|string|max:255',
            'storage_capacity' => 'nullable|integer|min:0',
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Location updated successfully!');
    }

    /**
     * Display the specified location with its assets.
     */
    public function show(Request $request, Location $location)
    {
        $location->load([
            'assets' => function ($q) {
                $q->with(['category', 'type'])->orderBy('name');
            }
        ])->loadCount([
            'assets',
            'assets as available_assets_count' => function ($q) {
                $q->where('status', 'available');
            }
        ]);

        if ($request->wantsJson()) {
            return response()->json($location);
        }

        return redirect()->route('locations.index', ['view_location' => $location->id]);
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(Request $request, Location $location)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($location->assets()->exists()) {
            $count = $location->assets()->count();
            return redirect()->route('locations.index')
                ->with('error', "Cannot delete location \"{$location->name}\" because {$count} asset(s) are currently kept in it. Please relocate or reassign the assets first.");
        }

        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', 'Location deleted successfully!');
    }

    /**
     * Relocate one or more assets to another facility.
     */
    public function relocateAsset(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->isViewer() || $user->hasRole('viewer', 'staff')) {
            abort(403, 'Unauthorized action. Read-only users cannot relocate assets.');
        }

        // Support both single asset_id and array asset_ids
        if ($request->has('asset_id') && !$request->has('asset_ids')) {
            $request->merge(['asset_ids' => [$request->input('asset_id')]]);
        }

        $validated = $request->validate([
            'asset_ids'        => 'required|array|min:1',
            'asset_ids.*'      => 'required|exists:assets,id',
            'location_id'      => 'required|exists:locations,id',
            'notes'            => 'nullable|string|max:500',
            'return_to_source' => 'nullable|boolean',
        ]);

        $newLocation = Location::findOrFail($validated['location_id']);
        $notes = trim($validated['notes'] ?? '');
        $notesSuffix = $notes !== '' ? " Reason: {$notes}" : '';

        $relocatedCount = 0;
        $relocatedCodes = [];
        $firstSourceLocationId = null;

        DB::transaction(function () use ($validated, $newLocation, $notesSuffix, $user, &$relocatedCount, &$relocatedCodes, &$firstSourceLocationId) {
            $assets = Asset::with('location')->whereIn('id', $validated['asset_ids'])->get();

            foreach ($assets as $asset) {
                if ($asset->location_id == $newLocation->id) {
                    continue;
                }

                $oldLocation = $asset->location;
                if (!$firstSourceLocationId && $oldLocation) {
                    $firstSourceLocationId = $oldLocation->id;
                }

                $oldLocationName = $oldLocation ? $oldLocation->name : 'Unassigned';
                $oldValues = ['location_id' => $asset->location_id];

                $asset->update(['location_id' => $newLocation->id]);
                $newValues = ['location_id' => $newLocation->id];

                AuditTrailService::log(
                    'location_changed',
                    $asset,
                    "Asset [{$asset->code} - {$asset->name}] relocated from '{$oldLocationName}' to '{$newLocation->name}'.{$notesSuffix}",
                    $oldValues,
                    $newValues,
                    $user->id
                );

                $relocatedCount++;
                $relocatedCodes[] = $asset->code;
            }
        });

        if ($relocatedCount === 0) {
            return redirect()->back()->with('warning', "Selected asset(s) are already stored at {$newLocation->name}.");
        }

        $codesStr = count($relocatedCodes) <= 3 
            ? implode(', ', $relocatedCodes) 
            : count($relocatedCodes) . ' assets';

        $returnLocationId = (!empty($validated['return_to_source']) && $firstSourceLocationId)
            ? $firstSourceLocationId
            : $newLocation->id;

        return redirect()->route('locations.index', ['view_location' => $returnLocationId])
            ->with('success', "Asset ({$codesStr}) successfully relocated to {$newLocation->name}!");
    }
}
