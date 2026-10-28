<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AuditLog;
use App\Models\Deployment;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Supported standard report types.
     */
    public static function reportTypes(): array
    {
        return [
            'inventory' => [
                'title'       => 'Master Asset Inventory',
                'description' => 'Complete list of all barangay disaster assets with location, cost, condition, and status.',
                'icon'        => 'clipboard-list',
            ],
            'readiness' => [
                'title'       => 'Ready-to-Deploy Assets',
                'description' => 'Emergency equipment in good or excellent condition ready for immediate field dispatch.',
                'icon'        => 'check-circle',
            ],
            'deployment_history' => [
                'title'       => 'Deployments & Returns History',
                'description' => 'Active field deployments and returned equipment with return dates and condition ratings.',
                'icon'        => 'truck',
            ],
            'maintenance_overdue' => [
                'title'       => 'Maintenance & Work Orders',
                'description' => 'Scheduled, pending, and in-progress preventive maintenance work orders.',
                'icon'        => 'wrench',
            ],
            'maintenance_expenses' => [
                'title'       => 'Maintenance Repair Costs',
                'description' => 'Summary of repair expenses, replacement parts used, and completed maintenance costs.',
                'icon'        => 'currency-dollar',
            ],
            'inspections' => [
                'title'       => 'Equipment Inspections',
                'description' => 'Physical inspection findings, pass/fail ratings, and scheduled next inspection dates.',
                'icon'        => 'shield-check',
            ],
            'high_risk' => [
                'title'       => 'Damaged & High-Risk Assets',
                'description' => 'Damaged, lost, or out-of-service equipment requiring urgent repair or replacement.',
                'icon'        => 'exclamation-triangle',
            ],
            'decommissioned' => [
                'title'       => 'Retired & Disposed Assets',
                'description' => 'Assets officially decommissioned or retired from service with authorization records.',
                'icon'        => 'archive',
            ],
            'audit_trail' => [
                'title'       => 'Activity Audit Trail',
                'description' => 'Full activity log of user actions, custody transitions, and system changes.',
                'icon'        => 'fingerprint',
            ],
        ];
    }

    /**
     * Display the Reports Center hub and report previews.
     */
    public function index(Request $request)
    {
        $types = static::reportTypes();
        $selectedType = $request->get('type', 'inventory');
        if ($selectedType === 'active_deployments') {
            $selectedType = 'deployment_history';
        }
        if (!array_key_exists($selectedType, $types)) {
            $selectedType = 'inventory';
        }

        $categories = AssetCategory::orderBy('name')->get();
        $data = $this->fetchReportData($selectedType, $request, 25);
        $summary = $this->getReportSummary($selectedType);

        return view('admin.reports.index', compact('types', 'selectedType', 'categories', 'data', 'summary'));
    }

    /**
     * Export selected report as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $types = static::reportTypes();
        $selectedType = $request->get('type', 'inventory');
        if ($selectedType === 'active_deployments') {
            $selectedType = 'deployment_history';
        }
        if (!array_key_exists($selectedType, $types)) {
            $selectedType = 'inventory';
        }

        $filename = 'bdes_report_' . $selectedType . '_' . now()->format('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($selectedType, $request) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            $this->streamReportCsv($selectedType, $request, $handle);

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Fetch report rows with eager loading.
     */
    protected function fetchReportData(string $type, Request $request, int $limit = 25)
    {
        return match ($type) {
            'inventory' => Asset::with(['category', 'type', 'location'])
                ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
                ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
                ->orderBy('name')
                ->paginate($limit)->withQueryString(),

            'readiness' => Asset::with(['category', 'location'])
                ->where('status', 'available')
                ->whereIn('condition', ['excellent', 'good'])
                ->orderBy('name')
                ->paginate($limit)->withQueryString(),

            'deployment_history', 'active_deployments' => Deployment::with(['asset.category', 'user', 'returnRecord.receivedByUser'])
                ->latest('deployment_date')
                ->paginate($limit)->withQueryString(),

            'maintenance_overdue' => Maintenance::with(['asset', 'assignedUser'])
                ->whereIn('status', ['pending', 'in_progress'])
                ->orderBy('start_date')
                ->paginate($limit)->withQueryString(),

            'maintenance_expenses' => Maintenance::with(['asset'])
                ->where('status', 'completed')
                ->orderByDesc('completed_at')
                ->paginate($limit)->withQueryString(),

            'inspections' => Inspection::with(['asset'])
                ->latest('inspection_date')
                ->paginate($limit)->withQueryString(),

            'high_risk' => Asset::with(['category', 'location'])
                ->where(function ($q) {
                    $q->whereIn('status', ['out_of_service', 'lost'])
                      ->orWhere('condition', 'poor');
                })
                ->orderBy('name')
                ->paginate($limit)->withQueryString(),

            'decommissioned' => Asset::with(['category', 'location', 'retiredBy'])
                ->where('status', 'retired')
                ->latest('retirement_date')
                ->paginate($limit)->withQueryString(),

            'audit_trail' => AuditLog::with(['user', 'auditable'])
                ->latest('id')
                ->paginate($limit)->withQueryString(),

            default => collect([]),
        };
    }

    /**
     * Stream CSV contents for each report type.
     */
    protected function streamReportCsv(string $type, Request $request, $handle): void
    {
        switch ($type) {
            case 'inventory':
                fputcsv($handle, ['Asset Code', 'Asset Name', 'Category', 'Location', 'Condition', 'Status', 'Acquisition Cost (PHP)', 'Serial Number', 'Purchase Date']);
                Asset::with(['category', 'location'])->chunk(200, function ($assets) use ($handle) {
                    foreach ($assets as $a) {
                        fputcsv($handle, [
                            $a->code, $a->name, $a->category?->name ?? 'N/A', $a->location?->name ?? 'N/A',
                            ucfirst($a->condition), ucfirst(str_replace('_', ' ', $a->status)),
                            number_format($a->acquisition_cost ?? 0, 2), $a->serial_number ?? 'N/A',
                            $a->purchase_date ? $a->purchase_date->format('Y-m-d') : 'N/A'
                        ]);
                    }
                });
                break;

            case 'readiness':
                fputcsv($handle, ['Asset Code', 'Asset Name', 'Category', 'Location', 'Condition', 'Status', 'Last Checked']);
                Asset::with(['category', 'location'])->where('status', 'available')->whereIn('condition', ['excellent', 'good'])->chunk(200, function ($assets) use ($handle) {
                    foreach ($assets as $a) {
                        fputcsv($handle, [
                            $a->code, $a->name, $a->category?->name ?? 'N/A', $a->location?->name ?? 'N/A',
                            ucfirst($a->condition), 'Ready for Field Dispatch', $a->updated_at->format('Y-m-d')
                        ]);
                    }
                });
                break;

            case 'deployment_history':
            case 'active_deployments':
                fputcsv($handle, ['Asset Code', 'Asset Name', 'Deployed By', 'Responsible / Borrower', 'Destination', 'Purpose', 'Deployment Date', 'Expected Return', 'Status', 'Returned Date', 'Return Condition', 'Received By']);
                Deployment::with(['asset', 'user', 'returnRecord.receivedByUser'])->chunk(200, function ($deps) use ($handle) {
                    foreach ($deps as $d) {
                        $ret = $d->returnRecord;
                        fputcsv($handle, [
                            $d->asset?->code ?? 'N/A',
                            $d->asset?->name ?? 'N/A',
                            $d->user?->name ?? 'N/A',
                            $d->responsible_person ?? 'N/A',
                            $d->location,
                            $d->purpose ?? 'N/A',
                            $d->deployment_date?->format('Y-m-d') ?? 'N/A',
                            $d->expected_return_date?->format('Y-m-d') ?? 'N/A',
                            $d->status === 'deployed' ? 'Active in Field' : ucfirst($d->status),
                            $ret?->return_date?->format('Y-m-d') ?? ($d->status === 'returned' ? 'Returned' : 'In Field'),
                            $ret?->condition ?? 'N/A',
                            $ret?->receivedByUser?->name ?? 'N/A',
                        ]);
                    }
                });
                break;

            case 'maintenance_overdue':
                fputcsv($handle, ['Work Order #', 'Asset Code', 'Asset Name', 'Maintenance Type', 'Priority', 'Status', 'Start Date', 'Assigned Technician', 'Est. Cost (PHP)']);
                Maintenance::with(['asset', 'assignedUser'])->chunk(200, function ($records) use ($handle) {
                    foreach ($records as $m) {
                        fputcsv($handle, [
                            $m->work_order_number ?? ('WO-' . $m->id), $m->asset?->code ?? 'N/A', $m->asset?->name ?? 'N/A',
                            ucfirst(str_replace('_', ' ', $m->type)), ucfirst($m->priority ?? 'medium'),
                            ucfirst(str_replace('_', ' ', $m->status)),
                            $m->start_date?->format('Y-m-d') ?? 'N/A',
                            $m->assignedUser?->name ?? 'Unassigned',
                            number_format($m->cost ?? 0, 2),
                        ]);
                    }
                });
                break;

            case 'maintenance_expenses':
                fputcsv($handle, ['Work Order #', 'Asset Code', 'Asset Name', 'Type', 'Cost (PHP)', 'Parts Used', 'Completed Date', 'Assigned Technician']);
                Maintenance::with(['asset', 'assignedUser'])->where('status', 'completed')->chunk(200, function ($records) use ($handle) {
                    foreach ($records as $m) {
                        fputcsv($handle, [
                            $m->work_order_number ?? ('WO-' . $m->id), $m->asset?->code ?? 'N/A', $m->asset?->name ?? 'N/A',
                            ucfirst(str_replace('_', ' ', $m->type)), number_format($m->cost ?? 0, 2),
                            $m->parts_used ?? 'None',
                            $m->completed_at ? $m->completed_at->format('Y-m-d') : ($m->end_date ? $m->end_date->format('Y-m-d') : 'N/A'),
                            $m->assignedUser?->name ?? 'N/A',
                        ]);
                    }
                });
                break;

            case 'inspections':
                fputcsv($handle, ['Asset Code', 'Asset Name', 'Inspector', 'Inspection Date', 'Result', 'Findings', 'Next Due Date']);
                Inspection::with(['asset'])->chunk(200, function ($records) use ($handle) {
                    foreach ($records as $i) {
                        fputcsv($handle, [
                            $i->asset?->code ?? 'N/A', $i->asset?->name ?? 'N/A', $i->inspector,
                            $i->inspection_date?->format('Y-m-d') ?? 'N/A', ucfirst($i->result),
                            $i->findings ?? 'None',
                            $i->next_due_date?->format('Y-m-d') ?? 'N/A',
                        ]);
                    }
                });
                break;

            case 'high_risk':
                fputcsv($handle, ['Asset Code', 'Asset Name', 'Category', 'Location', 'Condition', 'Status', 'Acquisition Cost (PHP)', 'Notes']);
                Asset::with(['category', 'location'])->where(function ($q) {
                    $q->whereIn('status', ['out_of_service', 'lost'])->orWhere('condition', 'poor');
                })->chunk(200, function ($records) use ($handle) {
                    foreach ($records as $a) {
                        fputcsv($handle, [
                            $a->code, $a->name, $a->category?->name ?? 'N/A', $a->location?->name ?? 'N/A',
                            ucfirst($a->condition), ucfirst(str_replace('_', ' ', $a->status)),
                            number_format($a->acquisition_cost ?? 0, 2), $a->notes ?? 'N/A',
                        ]);
                    }
                });
                break;

            case 'decommissioned':
                fputcsv($handle, ['Asset Code', 'Asset Name', 'Category', 'Retirement Date', 'Decommissioning Justification', 'Authorized By', 'Original Cost (PHP)']);
                Asset::with(['category', 'retiredBy'])->where('status', 'retired')->chunk(200, function ($records) use ($handle) {
                    foreach ($records as $a) {
                        fputcsv($handle, [
                            $a->code, $a->name, $a->category?->name ?? 'N/A',
                            $a->retirement_date ? $a->retirement_date->format('Y-m-d') : 'N/A',
                            $a->retirement_reason ?? 'Decommissioned by management',
                            $a->retiredBy?->name ?? 'Authorized Official',
                            number_format($a->acquisition_cost ?? 0, 2),
                        ]);
                    }
                });
                break;

            case 'audit_trail':
                fputcsv($handle, ['Log ID', 'Timestamp', 'User', 'Action', 'Target Type', 'Target Code', 'Description', 'IP Address']);
                AuditLog::with(['user', 'auditable'])->chunk(200, function ($records) use ($handle) {
                    foreach ($records as $l) {
                        fputcsv($handle, [
                            $l->id, $l->created_at->format('Y-m-d H:i:s'), $l->user?->name ?? 'System',
                            strtoupper($l->event), class_basename($l->auditable_type),
                            $l->auditable?->code ?? ('#' . $l->auditable_id),
                            $l->description, $l->ip_address ?? 'N/A',
                        ]);
                    }
                });
                break;
        }
    }

    /**
     * Get 3 highlight summary metrics for the given report type.
     */
    protected function getReportSummary(string $type): array
    {
        return match ($type) {
            'inventory' => [
                [
                    'label'   => 'Total Assets',
                    'value'   => number_format(Asset::count()),
                    'icon'    => 'fas fa-boxes-stacked',
                    'color'   => 'blue',
                    'subtext' => 'All registered barangay equipment',
                ],
                [
                    'label'   => 'Total Valuation',
                    'value'   => '₱' . number_format((float) Asset::sum('acquisition_cost'), 2),
                    'icon'    => 'fas fa-peso-sign',
                    'color'   => 'emerald',
                    'subtext' => 'Combined acquisition cost',
                ],
                [
                    'label'   => 'Asset Categories',
                    'value'   => number_format(AssetCategory::count()),
                    'icon'    => 'fas fa-tags',
                    'color'   => 'indigo',
                    'subtext' => 'Distinct classification groups',
                ],
            ],
            'readiness' => [
                [
                    'label'   => 'Ready-to-Deploy Assets',
                    'value'   => number_format(Asset::where('status', 'available')->whereIn('condition', ['excellent', 'good'])->count()),
                    'icon'    => 'fas fa-circle-check',
                    'color'   => 'emerald',
                    'subtext' => 'Available in good / excellent state',
                ],
                [
                    'label'   => 'Available in Storage',
                    'value'   => number_format(Asset::where('status', 'available')->count()),
                    'icon'    => 'fas fa-warehouse',
                    'color'   => 'blue',
                    'subtext' => 'Stationed and ready for assignment',
                ],
                [
                    'label'   => 'Readiness Rate',
                    'value'   => (Asset::count() > 0 ? round((Asset::where('status', 'available')->whereIn('condition', ['excellent', 'good'])->count() / Asset::count()) * 100, 1) : 100) . '%',
                    'icon'    => 'fas fa-gauge-high',
                    'color'   => 'indigo',
                    'subtext' => 'Fleet readiness percentage',
                ],
            ],
            'deployment_history', 'active_deployments' => [
                [
                    'label'   => 'Active Deployments',
                    'value'   => number_format(Deployment::where('status', 'active')->count()),
                    'icon'    => 'fas fa-truck-fast',
                    'color'   => 'amber',
                    'subtext' => 'Assets currently in field operations',
                ],
                [
                    'label'   => 'Returned Equipment',
                    'value'   => number_format(Deployment::where('status', 'returned')->count()),
                    'icon'    => 'fas fa-rotate-left',
                    'color'   => 'emerald',
                    'subtext' => 'Safely returned and checked in',
                ],
                [
                    'label'   => 'Total Deployments',
                    'value'   => number_format(Deployment::count()),
                    'icon'    => 'fas fa-clipboard-list',
                    'color'   => 'blue',
                    'subtext' => 'Lifetime deployment records',
                ],
            ],
            'maintenance_overdue' => [
                [
                    'label'   => 'Open Work Orders',
                    'value'   => number_format(Maintenance::whereIn('status', ['pending', 'in_progress'])->count()),
                    'icon'    => 'fas fa-wrench',
                    'color'   => 'amber',
                    'subtext' => 'Pending or actively in-service',
                ],
                [
                    'label'   => 'In-Progress Repairs',
                    'value'   => number_format(Maintenance::where('status', 'in_progress')->count()),
                    'icon'    => 'fas fa-screwdriver-wrench',
                    'color'   => 'blue',
                    'subtext' => 'Currently being repaired by staff',
                ],
                [
                    'label'   => 'Completed Restorations',
                    'value'   => number_format(Maintenance::where('status', 'completed')->count()),
                    'icon'    => 'fas fa-circle-check',
                    'color'   => 'emerald',
                    'subtext' => 'Successfully restored to service',
                ],
            ],
            'maintenance_expenses' => [
                [
                    'label'   => 'Total Repair Cost',
                    'value'   => '₱' . number_format((float) Maintenance::where('status', 'completed')->sum('cost'), 2),
                    'icon'    => 'fas fa-peso-sign',
                    'color'   => 'emerald',
                    'subtext' => 'Cumulative completed repair costs',
                ],
                [
                    'label'   => 'Completed Repairs',
                    'value'   => number_format(Maintenance::where('status', 'completed')->count()),
                    'icon'    => 'fas fa-clipboard-check',
                    'color'   => 'blue',
                    'subtext' => 'Finished work orders',
                ],
                [
                    'label'   => 'Average Cost / Repair',
                    'value'   => '₱' . number_format(Maintenance::where('status', 'completed')->count() > 0 ? ((float) Maintenance::where('status', 'completed')->sum('cost') / Maintenance::where('status', 'completed')->count()) : 0, 2),
                    'icon'    => 'fas fa-calculator',
                    'color'   => 'purple',
                    'subtext' => 'Average per completed order',
                ],
            ],
            'inspections' => [
                [
                    'label'   => 'Total Inspections',
                    'value'   => number_format(Inspection::count()),
                    'icon'    => 'fas fa-clipboard-check',
                    'color'   => 'blue',
                    'subtext' => 'Total equipment checks conducted',
                ],
                [
                    'label'   => 'Passed Inspections',
                    'value'   => number_format(Inspection::where('result', 'passed')->count()),
                    'icon'    => 'fas fa-shield-check',
                    'color'   => 'emerald',
                    'subtext' => 'Verified fit for disaster duty',
                ],
                [
                    'label'   => 'Failed / Flagged',
                    'value'   => number_format(Inspection::where('result', 'failed')->count()),
                    'icon'    => 'fas fa-triangle-exclamation',
                    'color'   => 'rose',
                    'subtext' => 'Flagged for repairs or replacement',
                ],
            ],
            'high_risk' => [
                [
                    'label'   => 'Out of Service / Lost',
                    'value'   => number_format(Asset::whereIn('status', ['out_of_service', 'lost'])->count()),
                    'icon'    => 'fas fa-ban',
                    'color'   => 'rose',
                    'subtext' => 'Unavailable for emergency dispatch',
                ],
                [
                    'label'   => 'Poor Condition Assets',
                    'value'   => number_format(Asset::where('condition', 'poor')->count()),
                    'icon'    => 'fas fa-triangle-exclamation',
                    'color'   => 'amber',
                    'subtext' => 'Critical wear requiring attention',
                ],
                [
                    'label'   => 'Total High-Risk Items',
                    'value'   => number_format(Asset::where(function ($q) {
                        $q->whereIn('status', ['out_of_service', 'lost'])
                          ->orWhere('condition', 'poor');
                    })->count()),
                    'icon'    => 'fas fa-radiation',
                    'color'   => 'purple',
                    'subtext' => 'Requiring urgent administrative action',
                ],
            ],
            'decommissioned' => [
                [
                    'label'   => 'Retired Assets',
                    'value'   => number_format(Asset::where('status', 'retired')->count()),
                    'icon'    => 'fas fa-box-archive',
                    'color'   => 'gray',
                    'subtext' => 'Officially decommissioned assets',
                ],
                [
                    'label'   => 'Disposed Valuation',
                    'value'   => '₱' . number_format((float) Asset::where('status', 'retired')->sum('acquisition_cost'), 2),
                    'icon'    => 'fas fa-peso-sign',
                    'color'   => 'blue',
                    'subtext' => 'Original acquisition value of retired units',
                ],
                [
                    'label'   => 'Disposed This Year',
                    'value'   => number_format(Asset::where('status', 'retired')->whereYear('retirement_date', now()->year)->count()),
                    'icon'    => 'fas fa-calendar-xmark',
                    'color'   => 'amber',
                    'subtext' => 'Decommissioned in ' . now()->year,
                ],
            ],
            'audit_trail' => [
                [
                    'label'   => 'Total Audit Events',
                    'value'   => number_format(AuditLog::count()),
                    'icon'    => 'fas fa-list-check',
                    'color'   => 'blue',
                    'subtext' => 'Immutable system logs recorded',
                ],
                [
                    'label'   => 'Activity Logged Today',
                    'value'   => number_format(AuditLog::whereDate('created_at', today())->count()),
                    'icon'    => 'fas fa-clock',
                    'color'   => 'emerald',
                    'subtext' => 'Events within the last 24 hours',
                ],
                [
                    'label'   => 'Active Audited Users',
                    'value'   => number_format(AuditLog::distinct('user_id')->count('user_id')),
                    'icon'    => 'fas fa-users-gear',
                    'color'   => 'indigo',
                    'subtext' => 'Users who performed recorded actions',
                ],
            ],
            default => [],
        };
    }
}

