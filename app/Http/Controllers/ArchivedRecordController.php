<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ArchiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArchivedRecordController extends Controller
{
    /**
     * Ensure only authorized administrators can access archived records.
     */
    protected function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized action. Archived records are restricted to administrators.');
        }
    }

    /**
     * Display a centralized list of archived records across applicable modules.
     */
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $filters = [
            'module'      => $request->get('module', 'all'),
            'search'      => $request->get('search', ''),
            'deleted_by'  => $request->get('deleted_by', null),
            'date_filter' => $request->get('date_filter', 'all'),
            'date_from'   => $request->get('date_from', null),
            'date_to'     => $request->get('date_to', null),
        ];

        $records = ArchiveService::getArchivedRecords($filters, 15);
        $metrics = ArchiveService::getMetrics();
        $modules = ArchiveService::registry();
        $users = User::orderBy('name')->get();

        return view('tracking-and-reporting.archived-records.index', compact(
            'records',
            'metrics',
            'modules',
            'users',
            'filters'
        ));
    }

    /**
     * Fetch archived record details for the modal view via AJAX.
     */
    public function show(Request $request, string $module, string|int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        try {
            $details = ArchiveService::getRecordDetails($module, $id);
            return response()->json([
                'success' => true,
                'data'    => $details,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Restore an archived record to its original module.
     */
    public function restore(Request $request, string $module, string|int $id): RedirectResponse
    {
        $this->authorizeAdmin($request);

        try {
            $record = ArchiveService::restore($module, $id);
            $identifier = $record->code ?? $record->work_order_number ?? $record->name ?? "#{$record->id}";

            return redirect()->route('archived-records.index', $request->query())
                ->with('success', "Record [{$identifier}] restored successfully to its active module with relationships intact.");
        } catch (\Exception $e) {
            return redirect()->route('archived-records.index', $request->query())
                ->with('error', "Failed to restore record: {$e->getMessage()}");
        }
    }

    /**
     * Permanently remove an archived record with strict safety enforcement.
     */
    public function forceDelete(Request $request, string $module, string|int $id): RedirectResponse
    {
        $this->authorizeAdmin($request);

        try {
            ArchiveService::forceDelete($module, $id);

            return redirect()->route('archived-records.index', $request->query())
                ->with('success', 'Record permanently deleted from archives. Audit log updated.');
        } catch (\DomainException $e) {
            return redirect()->route('archived-records.index', $request->query())
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->route('archived-records.index', $request->query())
                ->with('error', "Could not permanently delete record: {$e->getMessage()}");
        }
    }
}

