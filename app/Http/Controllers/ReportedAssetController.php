<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportedAssetRequest;
use App\Http\Requests\UpdateReportedAssetRequest;
use App\Models\Asset;
use App\Models\ReportedAsset;
use App\Models\User;
use App\Services\ReportedAssetService;
use Illuminate\Http\Request;

class ReportedAssetController extends Controller
{
    /**
     * Display a listing of reports with dashboard summary.
     */
    public function index(Request $request)
    {
        $query = ReportedAsset::with(['asset', 'reporter', 'assignedPersonnel', 'confirmedByUser']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_name', 'like', "%{$search}%")
                  ->orWhere('asset_category', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('asset_category')) {
            $query->where('asset_category', $request->asset_category);
        }
        if ($request->filled('reported_type')) {
            $query->where('reported_type', $request->reported_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Time / Timeframe Filter
        if ($request->filled('timeframe')) {
            match ($request->timeframe) {
                'today'      => $query->whereDate('reported_date', \Carbon\Carbon::today()),
                'this_week'  => $query->whereBetween('reported_date', [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]),
                'this_month' => $query->whereMonth('reported_date', \Carbon\Carbon::now()->month)->whereYear('reported_date', \Carbon\Carbon::now()->year),
                'this_year'  => $query->whereYear('reported_date', \Carbon\Carbon::now()->year),
                default      => null,
            };
        }
        if ($request->filled('date_from')) {
            $query->whereDate('reported_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('reported_date', '<=', $request->date_to);
        }

        $reports = $query->latest()->paginate(10)->withQueryString();

        // Dashboard summary — counts per status
        $statusCounts = [];
        foreach (ReportedAssetService::statuses() as $status) {
            $statusCounts[$status] = ReportedAsset::where('status', $status)->count();
        }

        // Dropdown data
        $assetCategories = ReportedAsset::select('asset_category')->distinct()->orderBy('asset_category')->pluck('asset_category');
        $reportedTypes = ReportedAssetService::reportedTypes();
        $statuses = ReportedAssetService::statuses();
        $assets = Asset::with('category')->orderBy('name')->get();
        $locations = \App\Models\Location::orderBy('name')->pluck('name');
        $personnelOptions = User::where('is_active', true)->orderBy('name')->get();

        return view('asset-operations.reported-assets.index', compact(
            'reports',
            'statusCounts',
            'assetCategories',
            'reportedTypes',
            'statuses',
            'assets',
            'locations',
            'personnelOptions'
        ));
    }

    /**
     * Authorize that the user has permission to create or edit reports.
     */
    protected function authorizeEditor(Request $request): void
    {
        $user = $request->user();
        if (!$user || $user->isViewer() || $user->hasRole('viewer', 'staff')) {
            abort(403, 'Unauthorized action. Filing and modifying asset reports is restricted for staff members.');
        }
    }

    /**
     * Show form to create a report (restricted for staff).
     */
    public function create(Request $request)
    {
        $this->authorizeEditor($request);
        return redirect()->route('reported-assets.index');
    }

    /**
     * Display a single report with its full status history timeline.
     */
    public function show(Request $request, ReportedAsset $reportedAsset)
    {
        $reportedAsset->load(['asset', 'reporter', 'statusHistories.changedByUser', 'receipts', 'assignedPersonnel', 'confirmedByUser']);

        if ($request->wantsJson()) {
            return response()->json($reportedAsset);
        }

        $allowedNext = ReportedAssetService::allowedNextStatuses($reportedAsset->status);
        $personnelOptions = User::where('is_active', true)->orderBy('name')->get();

        return view('asset-operations.reported-assets.show', compact('reportedAsset', 'allowedNext', 'personnelOptions'));
    }

    /**
     * Confirm a reported asset and optionally assign personnel / transition status.
     */
    public function confirmReport(Request $request, ReportedAsset $reportedAsset)
    {
        $this->authorizeEditor($request);

        $validated = $request->validate([
            'personnel'   => ['nullable', 'array'],
            'personnel.*' => ['exists:users,id'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ]);

        $reportedAsset->confirmed_at = now();
        $reportedAsset->confirmed_by = auth()->id();

        // If personnel provided, sync them
        if ($request->has('personnel')) {
            $reportedAsset->assignedPersonnel()->sync($validated['personnel'] ?? []);
        }

        // Automatic status progression if pending or for review
        $oldStatus = $reportedAsset->status;
        $newStatus = null;
        if (in_array($reportedAsset->status, [ReportedAssetService::STATUS_PENDING, ReportedAssetService::STATUS_FOR_REVIEW])) {
            $newStatus = ReportedAssetService::STATUS_ONGOING;
            $reportedAsset->status = $newStatus;
        }

        $reportedAsset->save();

        // Record history log
        $notesText = 'Report confirmed by ' . auth()->user()->name . '.';
        if (!empty($validated['notes'])) {
            $notesText .= ' Notes: ' . $validated['notes'];
        }
        if (!empty($validated['personnel'])) {
            $personnelNames = User::whereIn('id', $validated['personnel'])->pluck('name')->implode(', ');
            $notesText .= ' Assigned personnel: ' . $personnelNames . '.';
        }

        $reportedAsset->statusHistories()->create([
            'from_status' => $oldStatus,
            'to_status'   => $newStatus ?? $oldStatus,
            'changed_by'  => auth()->id(),
            'notes'       => $notesText,
        ]);

        return redirect()->back()->with('success', 'Report successfully confirmed and personnel assigned.');
    }

    /**
     * Assign or update personnel assigned to work on the reported asset.
     */
    public function assignPersonnel(Request $request, ReportedAsset $reportedAsset)
    {
        $this->authorizeEditor($request);

        $validated = $request->validate([
            'personnel'   => ['nullable', 'array'],
            'personnel.*' => ['exists:users,id'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ]);

        $personnelIds = $validated['personnel'] ?? [];
        $reportedAsset->assignedPersonnel()->sync($personnelIds);

        $personnelNames = !empty($personnelIds)
            ? User::whereIn('id', $personnelIds)->pluck('name')->implode(', ')
            : 'None';

        $notesText = 'Assigned personnel updated by ' . auth()->user()->name . ': ' . $personnelNames;
        if (!empty($validated['notes'])) {
            $notesText .= '. Notes: ' . $validated['notes'];
        }

        $reportedAsset->statusHistories()->create([
            'from_status' => $reportedAsset->status,
            'to_status'   => $reportedAsset->status,
            'changed_by'  => auth()->id(),
            'notes'       => $notesText,
        ]);

        return redirect()->back()->with('success', 'Assigned personnel updated successfully.');
    }

    /**
     * Show form to edit a report (restricted for staff).
     */
    public function edit(Request $request, ReportedAsset $reportedAsset)
    {
        $this->authorizeEditor($request);
        return redirect()->route('reported-assets.index');
    }

    /**
     * Store a newly created report.
     */
    public function store(StoreReportedAssetRequest $request)
    {
        $this->authorizeEditor($request);

        $validated = $request->validated();
        $validated['reported_by'] = auth()->id();
        $validated['status'] = ReportedAssetService::STATUS_PENDING;
        $validated['reported_date'] = $validated['reported_date'] ?? now()->format('Y-m-d');

        $personnel = $validated['personnel'] ?? [];
        $confirmImmediately = $request->boolean('confirm_immediately');

        if ($confirmImmediately) {
            $validated['confirmed_at'] = now();
            $validated['confirmed_by'] = auth()->id();
            $validated['status'] = ReportedAssetService::STATUS_ONGOING;
        }

        unset($validated['personnel'], $validated['confirm_immediately'], $validated['receipts'], $validated['receipt_reference_number']);
        $report = ReportedAsset::create($validated);
        \App\Services\ReceiptService::attachFromRequest($report, $request);

        if (!empty($personnel)) {
            $report->assignedPersonnel()->sync($personnel);
        }

        // Log initial status
        $report->statusHistories()->create([
            'from_status' => null,
            'to_status'   => $report->status,
            'changed_by'  => auth()->id(),
            'notes'       => $confirmImmediately ? 'Report created and confirmed immediately.' : 'Report created.',
        ]);

        return redirect()->route('reported-assets.index')
            ->with('success', 'Report submitted successfully!');
    }

    /**
     * Update the specified report's editable fields (not status).
     */
    public function update(UpdateReportedAssetRequest $request, ReportedAsset $reportedAsset)
    {
        $this->authorizeEditor($request);

        $validated = $request->validated();
        if ($request->has('personnel')) {
            $reportedAsset->assignedPersonnel()->sync($validated['personnel'] ?? []);
            unset($validated['personnel']);
        }

        $reportedAsset->update($validated);

        return redirect()->route('reported-assets.index')
            ->with('success', 'Report updated successfully!');
    }

    /**
     * Remove the specified report (admin-only).
     */
    public function destroy(Request $request, ReportedAsset $reportedAsset)
    {
        $user = $request->user();
        if (!$user || $user->isViewer() || $user->hasRole('viewer', 'staff') || !$user->isAdmin()) {
            abort(403, 'Unauthorized. Only administrators can delete reported asset records.');
        }

        $reportedAsset->delete();

        return redirect()->route('reported-assets.index')
            ->with('success', 'Report deleted successfully!');
    }

    /**
     * Change the status of a report through the governed workflow.
     */
    public function changeStatus(Request $request, ReportedAsset $reportedAsset)
    {
        $user = $request->user();
        if (!$user || $user->isViewer() || $user->hasRole('viewer', 'staff')) {
            abort(403, 'Unauthorized action. Status changes require operational permissions.');
        }

        $validated = $request->validate([
            'new_status' => ['required', 'string'],
            'notes'      => ['nullable', 'string'],
        ]);

        $success = $reportedAsset->changeStatus(
            $validated['new_status'],
            auth()->id(),
            $validated['notes'] ?? null
        );

        if (!$success) {
            return redirect()->back()->withErrors([
                'new_status' => "Invalid status transition: {$reportedAsset->status} → {$validated['new_status']}.",
            ]);
        }

        return redirect()->route('reported-assets.show', $reportedAsset)
            ->with('success', "Status changed to \"{$validated['new_status']}\" successfully!");
    }
}
