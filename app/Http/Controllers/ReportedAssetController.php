<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportedAssetRequest;
use App\Http\Requests\UpdateReportedAssetRequest;
use App\Models\Asset;
use App\Models\ReportedAsset;
use App\Services\ReportedAssetService;
use Illuminate\Http\Request;

class ReportedAssetController extends Controller
{
    /**
     * Display a listing of reports with dashboard summary.
     */
    public function index(Request $request)
    {
        $query = ReportedAsset::with(['asset', 'reporter']);

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

        return view('admin.reported-assets.index', compact(
            'reports',
            'statusCounts',
            'assetCategories',
            'reportedTypes',
            'statuses',
            'assets',
            'locations'
        ));
    }

    /**
     * Display a single report with its full status history timeline.
     */
    public function show(ReportedAsset $reportedAsset)
    {
        $reportedAsset->load(['asset', 'reporter', 'statusHistories.changedByUser']);

        $allowedNext = ReportedAssetService::allowedNextStatuses($reportedAsset->status);

        return view('admin.reported-assets.show', compact('reportedAsset', 'allowedNext'));
    }

    /**
     * Store a newly created report.
     */
    public function store(StoreReportedAssetRequest $request)
    {
        $validated = $request->validated();
        $validated['reported_by'] = auth()->id();
        $validated['status'] = ReportedAssetService::STATUS_PENDING;

        $report = ReportedAsset::create($validated);

        // Log initial status
        $report->statusHistories()->create([
            'from_status' => null,
            'to_status'   => ReportedAssetService::STATUS_PENDING,
            'changed_by'  => auth()->id(),
            'notes'       => 'Report created.',
        ]);

        return redirect()->route('reported-assets.index')
            ->with('success', 'Report submitted successfully!');
    }

    /**
     * Update the specified report's editable fields (not status).
     */
    public function update(UpdateReportedAssetRequest $request, ReportedAsset $reportedAsset)
    {
        $validated = $request->validated();
        $reportedAsset->update($validated);

        return redirect()->route('reported-assets.index')
            ->with('success', 'Report updated successfully!');
    }

    /**
     * Remove the specified report (admin-only).
     */
    public function destroy(ReportedAsset $reportedAsset)
    {
        if (!auth()->user()->isAdmin()) {
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
        if (auth()->user()->hasRole('viewer')) {
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
