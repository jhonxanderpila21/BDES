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

        // Input sanitization
        if ($request->has('title')) {
            $request->merge(['title' => strip_tags(trim($request->title))]);
        }
        if ($request->has('notes')) {
            $request->merge(['notes' => strip_tags(trim($request->notes))]);
        }

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'document_type' => 'required|string|in:manual,warranty,registration,certificate,invoice,deed_of_donation,other',
            'document_file' => [
                'required',
                'file',
                'max:10240', // 10MB max limit
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp,txt',
            ],
            'expiry_date'   => 'nullable|date',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $file = $request->file('document_file');
        $originalBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalBase);
        $fileName = \Illuminate\Support\Str::limit($safeBase, 80, '') . '.' . strtolower($extension);

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
        Gate::authorize('view', $document->asset);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found on storage disk.');
        }

        $safeDownloadName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($document->file_name));

        return Storage::disk('public')->download($document->file_path, $safeDownloadName);
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
