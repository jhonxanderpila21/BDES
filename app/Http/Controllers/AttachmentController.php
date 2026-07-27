<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Asset;
use App\Models\AssetType;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Attachment::with(['asset', 'assetType']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        $attachments = $query->latest()->paginate(10)->withQueryString();
        $assets = Asset::orderBy('name')->get();
        $assetTypes = AssetType::orderBy('name')->get();

        return view('admin.attachments.index', compact('attachments', 'assets', 'assetTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'asset_type_id' => 'nullable|exists:asset_types,id',
            'asset_id' => 'nullable|exists:assets,id',
            'quantity' => 'nullable|integer|min:0',
            'condition' => 'nullable|string|max:255',
        ]);

        Attachment::create($validated);

        return redirect()->route('attachments.index')
            ->with('success', 'Attachment created successfully!');
    }

    public function update(Request $request, Attachment $attachment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'asset_type_id' => 'nullable|exists:asset_types,id',
            'asset_id' => 'nullable|exists:assets,id',
            'quantity' => 'nullable|integer|min:0',
            'condition' => 'nullable|string|max:255',
        ]);

        $attachment->update($validated);

        return redirect()->route('attachments.index')
            ->with('success', 'Attachment updated successfully!');
    }

    public function destroy(Attachment $attachment)
    {
        $attachment->delete();

        return redirect()->route('attachments.index')
            ->with('success', 'Attachment deleted successfully!');
    }
}
