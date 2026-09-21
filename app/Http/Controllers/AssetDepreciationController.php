<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetDepreciationController extends Controller
{
    /**
     * Display a listing of assets with depreciation schedules and book values.
     */
    public function index(Request $request)
    {
        $query = Asset::with(['category', 'location'])
            ->whereNotNull('acquisition_cost')
            ->where('acquisition_cost', '>', 0);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $assets = $query->orderBy('name')->paginate(15)->withQueryString();

        // Calculate aggregate statistics for all eligible assets
        $allEligible = Asset::whereNotNull('acquisition_cost')
            ->where('acquisition_cost', '>', 0)
            ->get();

        $totalAcquisitionCost = $allEligible->sum(fn ($a) => (float) $a->acquisition_cost);
        $totalAccumulatedDepreciation = $allEligible->sum(fn ($a) => $a->accumulated_depreciation);
        $totalCurrentBookValue = $allEligible->sum(fn ($a) => $a->current_book_value);
        $totalAnnualDepreciation = $allEligible->sum(fn ($a) => $a->annual_depreciation);

        $categories = AssetCategory::orderBy('name')->get();

        return view('admin.financials.depreciation', compact(
            'assets',
            'categories',
            'totalAcquisitionCost',
            'totalAccumulatedDepreciation',
            'totalCurrentBookValue',
            'totalAnnualDepreciation'
        ));
    }

    /**
     * Update depreciation parameters for a specific asset.
     */
    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'useful_life_years' => 'required|integer|min:1|max:50',
            'salvage_value'     => 'nullable|numeric|min:0|lte:' . ($asset->acquisition_cost ?? 999999999),
        ]);

        $asset->update($validated);

        return redirect()->back()->with('success', "Depreciation parameters for '{$asset->name}' updated successfully.");
    }

    /**
     * Export the depreciation schedule to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $assets = Asset::with(['category', 'location'])
            ->whereNotNull('acquisition_cost')
            ->where('acquisition_cost', '>', 0)
            ->orderBy('name')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bdes_depreciation_schedule_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($assets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Asset Code',
                'Asset Name',
                'Category',
                'Purchase Date',
                'Acquisition Cost (PHP)',
                'Useful Life (Years)',
                'Age (Years)',
                'Calculated Salvage Value (PHP)',
                'Annual Depreciation (PHP)',
                'Accumulated Depreciation (PHP)',
                'Current Book Value (PHP)',
                'Depreciation %',
                'Status',
            ]);

            foreach ($assets as $asset) {
                fputcsv($handle, [
                    $asset->code,
                    $asset->name,
                    $asset->category?->name ?? 'Uncategorized',
                    $asset->purchase_date?->format('Y-m-d') ?? 'N/A',
                    number_format($asset->acquisition_cost, 2, '.', ''),
                    $asset->effective_useful_life,
                    $asset->age_in_years,
                    number_format($asset->calculated_salvage_value, 2, '.', ''),
                    number_format($asset->annual_depreciation, 2, '.', ''),
                    number_format($asset->accumulated_depreciation, 2, '.', ''),
                    number_format($asset->current_book_value, 2, '.', ''),
                    $asset->depreciation_percentage . '%',
                    ucfirst(str_replace('_', ' ', $asset->status)),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}

