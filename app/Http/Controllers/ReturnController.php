<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetReturn::with('asset');

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

        $returns = $query->latest()->paginate(10)->withQueryString();
        $assets = Asset::where('status', 'deployed')->orderBy('name')->get();

        return view('admin.returns.index', compact('returns', 'assets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => [
                'required',
                Rule::exists('assets', 'id')->where('status', 'deployed'),
            ],
            'returned_by' => 'nullable|string|max:255',
            'return_date' => 'required|date',
            'condition' => 'required|in:excellent,good,fair,poor',
            'damage_notes' => 'nullable|string',
            'missing_accessories' => 'nullable|string',
            'actual_location' => 'nullable|string|max:255',
            'status' => 'required|in:received,damage_noted,incomplete_accessories',
        ]);

        DB::transaction(function () use ($validated) {
            AssetReturn::create($validated);

            $asset = Asset::find($validated['asset_id']);
            if ($asset) {
                $asset->status = 'available';
                $asset->condition = $validated['condition'];
                $asset->save();
            }
        });

        return redirect()->route('returns.index')
            ->with('success', 'Return recorded successfully and asset status updated!');
    }

    public function update(Request $request, AssetReturn $return)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'returned_by' => 'nullable|string|max:255',
            'return_date' => 'required|date',
            'condition' => 'required|in:excellent,good,fair,poor',
            'damage_notes' => 'nullable|string',
            'missing_accessories' => 'nullable|string',
            'actual_location' => 'nullable|string|max:255',
            'status' => 'required|in:received,damage_noted,incomplete_accessories',
        ]);

        DB::transaction(function () use ($validated, $return) {
            $oldAssetId = $return->asset_id;

            $return->update($validated);

            // If the return was reassigned to a different asset,
            // put the old asset back to 'deployed'.
            if ($oldAssetId !== (int) $validated['asset_id']) {
                if ($oldAsset = Asset::find($oldAssetId)) {
                    $oldAsset->status = 'deployed';
                    $oldAsset->save();
                }
            }

            $asset = Asset::find($validated['asset_id']);
            if ($asset) {
                $asset->status = 'available';
                $asset->condition = $validated['condition'];
                $asset->save();
            }
        });

        return redirect()->route('returns.index')
            ->with('success', 'Return record updated successfully!');
    }

    public function destroy(AssetReturn $return)
    {
        DB::transaction(function () use ($return) {
            $asset = $return->asset;
            $return->delete();

            // Optional: reverting is a business-logic decision, not a syntax fix —
            // remove this block if a deleted return record should NOT re-deploy the asset.
            if ($asset) {
                $asset->status = 'deployed';
                $asset->save();
            }
        });

        return redirect()->route('returns.index')
            ->with('success', 'Return record deleted successfully!');
    }
}