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
                'title'       => 'Comprehensive Master Asset Inventory',
                'description' => 'Complete register of all barangay disaster equipment with location, cost, and status.',
                'icon'        => 'clipboard-list',
            ],
            'readiness' => [
                'title'       => 'Deployment Readiness & Available Assets',
                'description' => 'Assets immediately ready for disaster dispatch and emergency operations.',
                'icon'        => 'check-circle',
            ],
            'active_deployments' => [
                'title'       => 'Current Active Field Deployments',
                'description' => 'Assets currently deployed in the field with responsible custodians and stations.',
                'icon'        => 'truck',
            ],
            'deployment_history' => [
                'title'       => 'Deployment History & Return Compliance',
                'description' => 'Full log of past missions, return timestamps, and return condition assessments.',
                'icon'        => 'clock',
            ],
            'maintenance_overdue' => [
                'title'       => 'Maintenance Schedule & Work Orders',
                'description' => 'Pending, overdue, and scheduled preventive maintenance tasks and work orders.',
                'icon'        => 'wrench',
            ],
            'maintenance_expenses' => [
                'title'       => 'Maintenance Cost & Expense Summary',
                'description' => 'Breakdown of repair expenditures, spare parts consumed, and service charges.',
                'icon'        => 'currency-dollar',
            ],
            'inspections' => [
                'title'       => 'Physical Asset Condition & Inspection Ratings',
                'description' => 'Quality ratings, safety findings, and scheduled re-inspection timelines.',
                'icon'        => 'shield-check',
            ],
            'high_risk' => [
                'title'       => 'High-Risk / Damaged / Out-of-Service Assets',
                'description' => 'Equipment requiring urgent rehabilitation, replacement, or write-off.',
                'icon'        => 'exclamation-triangle',
            ],
            'decommissioned' => [
                'title'       => 'Decommissioned & Retired Assets Register',
                'description' => 'Assets retired from active duty with authorization signatures and justifications.',
                'icon'        => 'archive',
            ],
            'audit_trail' => [
                'title'       => 'Audit Trail & Custody Movement History',
                'description' => 'Immutable audit log of user actions, status transitions, and data modifications.',
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
        if (!array_key_exists($selectedType, $types)) {
            $selectedType = 'inventory';
        }

        $categories = AssetCategory::orderBy('name')->get();
        $data = $this->fetchReportData($selectedType, $request, 25);

        return view('admin.reports.index', compact('types', 'selectedType', 'categories', 'data'));
    }

    /**
     * Export selected report as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $types = static::reportTypes();
        $selectedType = $request->get('type', 'inventory');
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

            'active_deployments' => Deployment::with(['asset.category', 'user'])
                ->where('status', 'deployed')
                ->latest('deployment_date')
                ->paginate($limit)->withQueryString(),

            'deployment_history' => Deployment::with(['asset', 'user', 'returnRecord.receivedByUser'])
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

            case 'active_deployments':
                fputcsv($handle, ['Asset Code', 'Asset Name', 'Deployed By', 'Responsible Person', 'Destination', 'Purpose', 'Deployment Date', 'Expected Return']);
                Deployment::with(['asset', 'user'])->where('status', 'deployed')->chunk(200, function ($deps) use ($handle) {
                    foreach ($deps as $d) {
                        fputcsv($handle, [
                            $d->asset?->code ?? 'N/A', $d->asset?->name ?? 'N/A', $d->user?->name ?? 'N/A',
                            $d->responsible_person ?? 'N/A', $d->location, $d->purpose,
                            $d->deployment_date?->format('Y-m-d') ?? 'N/A',
                            $d->expected_return_date?->format('Y-m-d') ?? 'N/A'
                        ]);
                    }
                });
                break;

            case 'deployment_history':
                fputcsv($handle, ['Asset Code', 'Asset Name', 'Destination', 'Deployed Date', 'Expected Return', 'Status', 'Returned On', 'Condition Upon Return', 'Received By']);
                Deployment::with(['asset', 'returnRecord.receivedByUser'])->chunk(200, function ($deps) use ($handle) {
                    foreach ($deps as $d) {
                        $ret = $d->returnRecord;
                        fputcsv($handle, [
                            $d->asset?->code ?? 'N/A', $d->asset?->name ?? 'N/A', $d->location,
                            $d->deployment_date?->format('Y-m-d') ?? 'N/A',
                            $d->expected_return_date?->format('Y-m-d') ?? 'N/A',
                            ucfirst($d->status),
                            $ret?->return_date?->format('Y-m-d') ?? ($d->status === 'returned' ? 'Yes' : 'Pending'),
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
}

