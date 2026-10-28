<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetSalvageController extends Controller
{
    /**
     * Display a listing of salvage value records and residual valuations.
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
                  ->orWhere('salvage_notes', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Custom vs standard filter
        if ($salvageType = $request->input('salvage_type')) {
            if ($salvageType === 'custom') {
                $query->whereNotNull('salvage_value')->where('salvage_value', '>', 0);
            } elseif ($salvageType === 'standard') {
                $query->where(function ($q) {
                    $q->whereNull('salvage_value')->orWhere('salvage_value', '<=', 0);
                });
            }
        }

        $assets = $query->orderBy('name')->paginate(15)->withQueryString();

        // Calculate aggregate statistics
        $allEligible = Asset::whereNotNull('acquisition_cost')
            ->where('acquisition_cost', '>', 0)
            ->get();

        $totalSalvageValue = $allEligible->sum(fn ($a) => $a->calculated_salvage_value);
        $customSalvageCount = $allEligible->filter(fn ($a) => $a->salvage_value !== null && $a->salvage_value > 0)->count();
        $standardSalvageCount = $allEligible->count() - $customSalvageCount;
        $fullyDepreciatedCount = $allEligible->filter(fn ($a) => $a->current_book_value <= $a->calculated_salvage_value)->count();

        $categories = AssetCategory::orderBy('name')->get();

        return view('admin.financials.salvage', compact(
            'assets',
            'categories',
            'totalSalvageValue',
            'customSalvageCount',
            'standardSalvageCount',
            'fullyDepreciatedCount'
        ));
    }

    /**
     * Update salvage value and appraisal/residual notes for an asset.
     */
    public function update(Request $request, Asset $asset)
    {
        // Enforce capitalization threshold: Items below threshold cannot have a salvage value
        if (!$asset->is_capitalized_ppe) {
            $thresholdFormatted = number_format(\App\Services\PhilippineAccountingStandardService::CAPITALIZATION_THRESHOLD, 2);
            return redirect()->back()->with('error', "Items with acquisition cost below ₱{$thresholdFormatted} are classified as Semi-Expendable Property and cannot have a salvage value record.");
        }

        if ($request->has('salvage_value') && is_string($request->salvage_value)) {
            $clean = trim(str_replace([',', '₱', '$', 'PHP', ' '], '', $request->salvage_value));
            $request->merge(['salvage_value' => $clean === '' ? null : $clean]);
        }
        if ($request->has('salvage_notes')) {
            $request->merge(['salvage_notes' => strip_tags(trim($request->salvage_notes))]);
        }

        $validated = $request->validate([
            'salvage_value' => 'nullable|numeric|min:0|lte:' . ($asset->acquisition_cost ?? 999999999),
            'salvage_notes' => 'nullable|string|max:1000',
        ], [
            'salvage_value.lte' => 'The salvage value cannot exceed the original acquisition cost.',
        ]);

        $asset->update([
            'salvage_value' => $validated['salvage_value'] !== null ? $validated['salvage_value'] : null,
            'salvage_notes' => $validated['salvage_notes'] ?? null,
        ]);

        return redirect()->back()->with('success', "Salvage value record for '{$asset->name}' updated successfully.");
    }

    /**
     * Export salvage value records to CSV.
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
            'Content-Disposition' => 'attachment; filename="bdes_salvage_records_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($assets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Asset Code',
                'Asset Name',
                'Category',
                'COA Classification',
                'Acquisition Cost (PHP)',
                'Calculated Salvage Value (PHP)',
                'Salvage Determination Method',
                'Residual Notes / Justification',
                'Current Book Value (PHP)',
                'Status',
            ]);

            foreach ($assets as $asset) {
                if (!$asset->is_capitalized_ppe) {
                    $method = 'COA Semi-Expendable (₱0.00 Non-Salvageable)';
                } else {
                    $method = ($asset->salvage_value !== null && $asset->salvage_value > 0)
                        ? 'Custom Appraised Valuation'
                        : 'COA Standard 10% Residual';
                }

                fputcsv($handle, [
                    $asset->code,
                    $asset->name,
                    $asset->category?->name ?? 'Uncategorized',
                    $asset->coa_classification,
                    number_format($asset->acquisition_cost, 2, '.', ''),
                    number_format($asset->calculated_salvage_value, 2, '.', ''),
                    $method,
                    $asset->salvage_notes ?? 'None',
                    number_format($asset->current_book_value, 2, '.', ''),
                    ucfirst(str_replace('_', ' ', $asset->status)),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}

