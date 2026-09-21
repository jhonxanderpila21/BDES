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
        $query = AuditLog::with(['user', 'auditable'])->latest('id');

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

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
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

        $metrics = [
            'total_logs'     => AuditLog::count(),
            'today_logs'     => AuditLog::whereDate('created_at', today())->count(),
            'status_changes' => AuditLog::where('event', 'status_changed')->count(),
            'active_actors'  => AuditLog::distinct('user_id')->count('user_id'),
        ];

        $users = User::orderBy('name')->get();

        return view('admin.asset-history.index', compact('logs', 'metrics', 'users'));
    }

    /**
     * Export filtered audit history to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = AuditLog::with(['user', 'auditable'])->latest('id');

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

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $filename = 'audit_history_' . now()->format('Ymd_His') . '.csv';

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

