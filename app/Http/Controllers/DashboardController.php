<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Deployment;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Services\DashboardChartService;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardChartService $charts)
    {
    }

    /**
     * Display the executive dashboard with reconciled metrics and operational attention alerts.
     */
    public function index()
    {
        // Reconciled enterprise KPIs
        $stats = [
            'total_assets'       => Asset::count(),
            'total_value'        => Asset::sum('acquisition_cost') ?? 0,
            'available'          => Asset::where('status', 'available')->count(),
            'deployed'           => Asset::where('status', 'deployed')->count(),
            'under_maintenance'  => Asset::where('status', 'under_maintenance')->count(),
            'out_of_service'     => Asset::where('status', 'out_of_service')->count(),
            'lost'               => Asset::where('status', 'lost')->count(),
            'retired'            => Asset::where('status', 'retired')->count(),
            'due_for_inspection' => Inspection::where('status', 'scheduled')
                ->where('inspection_date', '<=', now()->addDays(7))
                ->count(),
            'inspections'        => Inspection::count(),
        ];

        // "Needs Attention" Operational Alerts
        $attentionItems = [
            'overdue_deployments' => Deployment::with(['asset', 'returnRecord'])
                ->where('status', 'active')
                ->whereNotNull('expected_return_date')
                ->where('expected_return_date', '<', now()->toDateString())
                ->get(),

            'overdue_maintenance' => Maintenance::with(['asset', 'assignedUser'])
                ->whereIn('status', ['scheduled', 'pending', 'in_progress'])
                ->whereNotNull('scheduled_date')
                ->where('scheduled_date', '<', now()->toDateString())
                ->get(),

            'high_risk_assets'    => Asset::with(['category', 'location'])
                ->where(function ($q) {
                    $q->whereIn('status', ['out_of_service', 'lost'])
                      ->orWhere('condition', 'poor');
                })
                ->where('status', '!=', 'retired')
                ->take(6)
                ->get(),
        ];

        // Real Activity Trail from Audit Logs
        $recentAuditLogs = AuditLog::with(['user', 'auditable'])
            ->latest('id')
            ->take(6)
            ->get();

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
            'attentionItems',
            'recentAuditLogs',
            'upcomingInspections',
            'maintenanceReminders',
            'assetsByCategory'
        ));
    }
}
