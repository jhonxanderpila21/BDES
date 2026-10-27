<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    /**
     * Stream a receipt (inline so images/PDFs open in the browser).
     */
    public function show(Receipt $receipt)
    {
        if (! Storage::disk(Receipt::DISK)->exists($receipt->file_path)) {
            return back()->with('error', 'The receipt file could not be found. It may have been removed.');
        }

        $safeName = preg_replace('/[^A-Za-z0-9_\.\-]/', '_', basename($receipt->file_name));

        return Storage::disk(Receipt::DISK)->response($receipt->file_path, $safeName, [
            'Content-Type'           => $receipt->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Remove a receipt. Only administrators or the original uploader may delete.
     */
    public function destroy(Receipt $receipt)
    {
        $user = auth()->user();
        if (! $user->isAdmin() && (int) $receipt->uploaded_by !== (int) $user->id) {
            return back()->with('error', 'You can only remove receipts that you uploaded.');
        }

        $receipt->delete();

        return back()->with('success', 'Receipt removed successfully.');
    }
}

