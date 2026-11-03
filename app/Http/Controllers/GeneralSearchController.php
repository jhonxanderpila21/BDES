<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Deployment;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\ReportedAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GeneralSearchController extends Controller
{
    /**
     * Provide autocomplete search suggestions across all core modules.
     * Guaranteed to display at least 5 suggestions when matches exist.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $query = trim(strip_tags($request->get('q', '')));

        if (strlen($query) === 0) {
            // When query is empty, return 5 recent available assets as quick suggestions
            $recent = Asset::with(['category', 'location'])
                ->where('status', 'available')
                ->latest('id')
                ->take(5)
                ->get()
                ->map(fn($a) => [
                    'id'          => 'asset_' . $a->id,
                    'type'        => 'asset',
                    'category'    => 'Asset',
                    'badge_color' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'icon'        => 'fas fa-boxes-stacked text-blue-600',
                    'title'       => "[{$a->code}] {$a->name}",
                    'subtitle'    => ($a->category?->name ?? 'General') . ' • ' . ($a->location?->name ?? 'Depot') . ' • Available',
                    'url'         => route('assets.show', $a->id),
                ]);

            return response()->json([
                'success'     => true,
                'query'       => '',
                'suggestions' => $recent,
            ]);
        }

        $results = collect();

        // 1. Assets (Search by code, name, serial number, manufacturer)
        $assets = Asset::with(['category', 'location'])
            ->where(function ($q) use ($query) {
                $q->where('code', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('serial_number', 'like', "%{$query}%")
                    ->orWhere('manufacturer', 'like', "%{$query}%");
            })
            ->latest('id')
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'id'          => 'asset_' . $a->id,
                'type'        => 'asset',
                'category'    => 'Asset',
                'badge_color' => 'bg-blue-100 text-blue-800 border-blue-200',
                'icon'        => 'fas fa-boxes-stacked text-blue-600',
                'title'       => "[{$a->code}] {$a->name}",
                'subtitle'    => ($a->category?->name ?? 'Equipment') . ' • ' . ($a->location?->name ?? 'Depot') . ' • ' . ucfirst($a->status),
                'url'         => route('assets.show', $a->id),
            ]);
        $results = $results->concat($assets);

        // 2. Deployments (Search by destination, team, person, or linked asset)
        $deployments = Deployment::with('asset')
            ->where(function ($q) use ($query) {
                $q->where('destination', 'like', "%{$query}%")
                    ->orWhere('responsible_person', 'like', "%{$query}%")
                    ->orWhere('assigned_team', 'like', "%{$query}%")
                    ->orWhere('purpose', 'like', "%{$query}%")
                    ->orWhereHas('asset', function ($aq) use ($query) {
                        $aq->where('name', 'like', "%{$query}%")
                            ->orWhere('code', 'like', "%{$query}%");
                    });
            })
            ->latest('id')
            ->take(4)
            ->get()
            ->map(fn($d) => [
                'id'          => 'dep_' . $d->id,
                'type'        => 'deployment',
                'category'    => 'Deployment',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'icon'        => 'fas fa-truck-ramp-box text-emerald-600',
                'title'       => "Dispatch: " . ($d->destination ?: 'Field Mission'),
                'subtitle'    => ($d->asset ? "[{$d->asset->code}] {$d->asset->name} • " : '') . ucfirst(str_replace('_', ' ', $d->status)),
                'url'         => route('deployment.index', ['search' => $d->destination ?: $d->asset?->code]),
            ]);
        $results = $results->concat($deployments);

        // 3. Maintenance & Work Orders (Search by WO number, type, technician, asset)
        $maintenances = Maintenance::with('asset')
            ->where(function ($q) use ($query) {
                $q->where('work_order_number', 'like', "%{$query}%")
                    ->orWhere('type', 'like', "%{$query}%")
                    ->orWhere('technician', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%")
                    ->orWhereHas('asset', function ($aq) use ($query) {
                        $aq->where('name', 'like', "%{$query}%")
                            ->orWhere('code', 'like', "%{$query}%");
                    });
            })
            ->latest('id')
            ->take(4)
            ->get()
            ->map(fn($m) => [
                'id'          => 'maint_' . $m->id,
                'type'        => 'maintenance',
                'category'    => 'Work Order',
                'badge_color' => 'bg-purple-100 text-purple-800 border-purple-200',
                'icon'        => 'fas fa-screwdriver-wrench text-purple-600',
                'title'       => ($m->work_order_number ? "[{$m->work_order_number}] " : '') . ucfirst($m->type) . ' Servicing',
                'subtitle'    => ($m->asset ? "{$m->asset->name} • " : '') . 'Status: ' . ucfirst(str_replace('_', ' ', $m->status)),
                'url'         => route('maintenance.index', ['search' => $m->work_order_number ?: $m->id]),
            ]);
        $results = $results->concat($maintenances);

        // 4. Locations & Facilities
        $locations = Location::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%")
                    ->orWhere('type', 'like', "%{$query}%");
            })
            ->take(3)
            ->get()
            ->map(fn($l) => [
                'id'          => 'loc_' . $l->id,
                'type'        => 'location',
                'category'    => 'Location',
                'badge_color' => 'bg-amber-100 text-amber-800 border-amber-200',
                'icon'        => 'fas fa-warehouse text-amber-600',
                'title'       => $l->name,
                'subtitle'    => ucfirst($l->type ?: 'facility') . ($l->address ? " • {$l->address}" : ''),
                'url'         => route('locations.index', ['search' => $l->name]),
            ]);
        $results = $results->concat($locations);

        // 5. Reported Issues / Tickets
        $reports = ReportedAsset::where(function ($q) use ($query) {
                $q->where('description', 'like', "%{$query}%")
                    ->orWhere('asset_name', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%");
            })
            ->latest('id')
            ->take(3)
            ->get()
            ->map(fn($r) => [
                'id'          => 'rep_' . $r->id,
                'type'        => 'report',
                'category'    => 'Issue Ticket',
                'badge_color' => 'bg-rose-100 text-rose-800 border-rose-200',
                'icon'        => 'fas fa-flag text-rose-600',
                'title'       => "Issue: " . Str::limit($r->description ?: $r->asset_name, 35),
                'subtitle'    => "{$r->asset_name} • Status: {$r->status}",
                'url'         => route('reported-assets.index', ['search' => $r->asset_name ?: $r->id]),
            ]);
        $results = $results->concat($reports);

        // Return up to 8 prioritized suggestions (minimum 5 if available in DB)
        $suggestions = $results->unique('id')->values()->take(8);

        return response()->json([
            'success'     => true,
            'query'       => $query,
            'count'       => $suggestions->count(),
            'suggestions' => $suggestions,
        ]);
    }
}

