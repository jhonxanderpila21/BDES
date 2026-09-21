<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetDocument;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AssetDocumentController extends Controller
{
    /**
     * Store an uploaded digital document for an asset.
     */
    public function store(Request $request, Asset $asset)
    {
        Gate::authorize('uploadDocument', $asset);

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'document_type' => 'required|string|in:manual,warranty,registration,certificate,invoice,deed_of_donation,other',
            'document_file' => 'required|file|max:20480', // 20MB max
            'expiry_date'   => 'nullable|date',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $file = $request->file('document_file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('asset-documents/' . $asset->id, 'public');

        $document = AssetDocument::create([
            'asset_id'      => $asset->id,
            'title'         => $validated['title'],
            'document_type' => $validated['document_type'],
            'file_path'     => $filePath,
            'file_name'     => $fileName,
            'file_size'     => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
            'expiry_date'   => $validated['expiry_date'] ?? null,
            'uploaded_by'   => auth()->id(),
            'notes'         => $validated['notes'] ?? null,
        ]);

        AuditTrailService::log('updated', $asset, "Document [{$document->title}] ({$document->document_type}) uploaded to asset dossier.", null, ['document_id' => $document->id, 'title' => $document->title]);

        return redirect()->route('assets.show', $asset)
            ->with('success', "Document [{$document->title}] uploaded successfully!");
    }

    /**
     * Download an asset document.
     */
    public function download(AssetDocument $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found on storage disk.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Delete an asset document.
     */
    public function destroy(AssetDocument $document)
    {
        $asset = $document->asset;
        Gate::authorize('deleteDocument', $asset);

        $title = $document->title;

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        AuditTrailService::log('updated', $asset, "Document [{$title}] removed from asset dossier.");

        return redirect()->route('assets.show', $asset)
            ->with('success', "Document [{$title}] deleted successfully.");
    }
}
