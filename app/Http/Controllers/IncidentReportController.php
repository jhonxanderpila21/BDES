<?php

namespace App\Http\Controllers;

use App\Models\IncidentReport;
use App\Models\Asset;
use Illuminate\Http\Request;

class IncidentReportController extends Controller
{
    public function index(Request $request)
    {
        $query = IncidentReport::with('asset');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('asset', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $incidentReports = $query->latest()->paginate(10)->withQueryString();
        $assets = Asset::orderBy('name')->get();

        return view('admin.incident-reports.index', compact('incidentReports', 'assets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'nullable|exists:assets,id',
            'type' => 'nullable|in:loss,damage,theft,misuse,expiration',
            'severity' => 'required|in:critical,high,medium,low',
            'reported_by' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:open,investigating,resolved,closed',
        ]);

        IncidentReport::create($validated);

        // If asset is damaged or lost, update its condition/status
        if ($validated['asset_id'] && in_array($validated['type'], ['loss', 'theft', 'damage'])) {
            $asset = Asset::find($validated['asset_id']);
            if (in_array($validated['type'], ['loss', 'theft'])) {
                $asset->status = 'out_of_service';
                $asset->condition = 'poor';
            } elseif ($validated['type'] === 'damage') {
                $asset->condition = 'poor';
                $asset->status = 'under_maintenance';
            }
            $asset->save();
        }

        return redirect()->route('incident-reports.index')
            ->with('success', 'Incident report created successfully!');
    }

    public function update(Request $request, IncidentReport $incidentReport)
    {
        $validated = $request->validate([
            'asset_id' => 'nullable|exists:assets,id',
            'type' => 'nullable|in:loss,damage,theft,misuse,expiration',
            'severity' => 'required|in:critical,high,medium,low',
            'reported_by' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:open,investigating,resolved,closed',
        ]);

        $incidentReport->update($validated);

        return redirect()->route('incident-reports.index')
            ->with('success', 'Incident report updated successfully!');
    }

    public function destroy(IncidentReport $incidentReport)
    {
        $incidentReport->delete();

        return redirect()->route('incident-reports.index')
            ->with('success', 'Incident report deleted successfully!');
    }
}
