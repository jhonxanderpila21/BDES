<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Deployment;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\IncidentReport;
use App\Services\DashboardChartService;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardChartService $charts)
    {
    }

    /**
     * Display the dashboard with live statistics.
     */
    public function index()
    {
        $stats = [
            'total_assets' => Asset::count(),
            'available' => Asset::where('status', 'available')->count(),
            'deployed' => Asset::where('status', 'deployed')->count(),
            'under_maintenance' => Asset::where('status', 'under_maintenance')->count(),
            'due_for_inspection' => Inspection::where('status', 'scheduled')
                ->where('inspection_date', '<=', now()->addDays(7))
                ->count(),
            'incidents' => IncidentReport::whereIn('status', ['open', 'investigating'])->count(),
        ];

        // Recent activities — last 10 across all modules
        $recentDeployments = Deployment::with('asset')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($d) => [
                'type' => 'deployment',
                'icon' => 'fas fa-truck',
                'color' => 'green',
                'title' => 'Asset Deployed',
                'description' => ($d->asset->name ?? 'Unknown') . ' to ' . $d->destination,
                'time' => $d->created_at,
            ]);

        $recentMaintenance = Maintenance::with('asset')
            ->where('status', 'completed')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($m) => [
                'type' => 'maintenance',
                'icon' => 'fas fa-wrench',
                'color' => 'yellow',
                'title' => 'Maintenance Completed',
                'description' => $m->asset->name ?? 'Unknown',
                'time' => $m->created_at,
            ]);

        $recentActivities = $recentDeployments
            ->concat($recentMaintenance)
            ->sortByDesc('time')
            ->take(5)
            ->values();

        // Upcoming inspections
        $upcomingInspections = Inspection::with('asset')
            ->whereIn('status', ['scheduled'])
            ->orderBy('inspection_date')
            ->take(5)
            ->get();

        // Maintenance reminders
        $maintenanceReminders = Maintenance::with('asset')
            ->whereIn('status', ['scheduled', 'pending'])
            ->orderBy('scheduled_date')
            ->take(5)
            ->get();

        // Chart data
        $assetsByCategory = $this->charts->assetsByCategory();

        return view('admin.dashboard.index', compact(
            'stats',
            'recentActivities',
            'upcomingInspections',
            'maintenanceReminders',
            'assetsByCategory'
        ));
    }
}
