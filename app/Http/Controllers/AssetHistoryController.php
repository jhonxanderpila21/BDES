<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetHistoryController extends Controller
{
    /**
     * Display a searchable, filterable log of system audit history.
     */
    public function index(Request $request)
    {
        $currentUser = $request->user();
        $isAdmin = $currentUser && $currentUser->isAdmin();
        $isViewer = $currentUser && ($currentUser->isViewer() || $currentUser->hasRole('viewer', 'staff'));

        // Staff members and Administrators can view all system audit logs.
        // Other non-admin operational roles remain scoped to their own account.
        $canViewAllLogs = $isAdmin || $isViewer;

        $query = AuditLog::with(['user', 'auditable'])->latest('id');

        // Scoping: Non-admin users (except staff) are strictly scoped to their own account's audit logs
        if (!$canViewAllLogs) {
            $query->where('user_id', $currentUser->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('auditable_type', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uQ) use ($search) {
                      $uQ->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('auditable_type')) {
            $type = $request->auditable_type;
            if (!str_contains($type, '\\')) {
                $type = 'App\\Models\\' . $type;
            }
            $query->where('auditable_type', $type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Metrics: Scoped appropriately to account or system
        $metricsBase = AuditLog::query();
        if (!$canViewAllLogs) {
            $metricsBase->where('user_id', $currentUser->id);
        }

        $metrics = [
            'total_logs'     => (clone $metricsBase)->count(),
            'today_logs'     => (clone $metricsBase)->whereDate('created_at', today())->count(),
            'status_changes' => (clone $metricsBase)->where('event', 'status_changed')->count(),
            'active_actors'  => $canViewAllLogs ? AuditLog::distinct('user_id')->count('user_id') : 1,
        ];

        $users = $canViewAllLogs ? User::orderBy('name')->get() : collect([$currentUser]);

        return view('admin.asset-history.index', compact('logs', 'metrics', 'users', 'isAdmin', 'isViewer'));
    }

    /**
     * Export filtered audit history to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $currentUser = $request->user();
        if (!$currentUser || $currentUser->isViewer() || $currentUser->hasRole('viewer', 'staff')) {
            abort(403, 'Unauthorized. Staff members are not authorized to export audit logs.');
        }

        $isAdmin = $currentUser && $currentUser->isAdmin();

        $query = AuditLog::with(['user', 'auditable'])->latest('id');

        // Scoping: Non-admin users are strictly scoped to their own account's audit logs
        if (!$isAdmin) {
            $query->where('user_id', $currentUser->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('auditable_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $prefix = $isAdmin ? 'audit_history_system_' : 'audit_history_account_' . $currentUser->id . '_';
        $filename = $prefix . now()->format('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Log ID',
                'Timestamp',
                'Performed By',
                'User Role',
                'Action / Event',
                'Target Type',
                'Target Reference',
                'Summary Description',
                'Previous Values',
                'New Values',
                'IP Address',
            ]);

            $query->chunk(200, function ($chunk) use ($handle) {
                foreach ($chunk as $log) {
                    $targetRef = 'N/A';
                    if ($log->auditable) {
                        $targetRef = $log->auditable->code ?? $log->auditable->name ?? '#' . $log->auditable_id;
                    }

                    fputcsv($handle, [
                        $log->id,
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->user?->name ?? 'System',
                        $log->user?->role ?? 'N/A',
                        strtoupper($log->event),
                        class_basename($log->auditable_type),
                        $targetRef,
                        $log->description,
                        $log->old_values ? json_encode($log->old_values) : '',
                        $log->new_values ? json_encode($log->new_values) : '',
                        $log->ip_address ?? 'N/A',
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}

