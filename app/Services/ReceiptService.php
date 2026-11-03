<?php

namespace App\Services;

use App\Models\Receipt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Central place for validating and persisting proof-of-cost uploads.
 *
 * Usage in a controller:
 *   $request->validate([...ReceiptService::rules()]);
 *   ReceiptService::attachFromRequest($model, $request);
 */
class ReceiptService
{
    public const MAX_FILES = 5;
    public const MAX_KB = 5120; // 5 MB per file
    public const MIMES = 'pdf,jpg,jpeg,png,webp';

    /**
     * Validation rules to merge into any request that accepts receipts.
     */
    public static function rules(): array
    {
        return [
            'receipts'                 => ['nullable', 'array', 'max:' . self::MAX_FILES],
            'receipts.*'               => ['file', 'max:' . self::MAX_KB, 'mimes:' . self::MIMES],
            'receipt_reference_number' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9\-\/#\s]*$/'],
        ];
    }

    public static function messages(): array
    {
        return [
            'receipts.max'                   => 'You can upload at most ' . self::MAX_FILES . ' receipt files at a time.',
            'receipts.*.max'                 => 'Each receipt file must not exceed 5 MB.',
            'receipts.*.mimes'               => 'Receipts must be a PDF or image (JPG, PNG, WEBP).',
            'receipts.*.file'                => 'One of the receipt uploads failed. Please try again.',
            'receipt_reference_number.regex' => 'The receipt/OR number may only contain letters, numbers, spaces, dashes, slashes and #.',
        ];
    }

    /**
     * Persist every uploaded receipt on the request and link it to $model.
     *
     * @return int number of receipts stored
     */
    public static function attachFromRequest(Model $model, Request $request): int
    {
        if (! $request->hasFile('receipts')) {
            return 0;
        }

        $files = $request->file('receipts');
        $files = is_array($files) ? $files : [$files];
        $reference = trim(strip_tags((string) $request->input('receipt_reference_number'))) ?: null;

        $stored = 0;
        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                self::store($model, $file, $reference);
                $stored++;
            }
        }

        return $stored;
    }

    public static function store(Model $model, UploadedFile $file, ?string $reference = null): Receipt
    {
        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $safe = Str::limit(preg_replace('/[^A-Za-z0-9_\-]/', '_', $base) ?: 'receipt', 80, '');

        $folder = 'receipts/' . Str::snake(class_basename($model)) . '/' . $model->getKey();
        $path = $file->store($folder, Receipt::DISK);

        return $model->receipts()->create([
            'reference_number' => $reference,
            'file_path'        => $path,
            'file_name'        => $safe . '.' . $ext,
            'file_size'        => $file->getSize(),
            'mime_type'        => $file->getMimeType(),
            'uploaded_by'      => auth()->id(),
        ]);
    }

    /**
     * Link existing receipts to another record (shares the same stored file).
     * Useful when one purchase receipt covers several records (bulk quantity).
     */
    public static function linkExisting(iterable $receipts, Model $target): void
    {
        foreach ($receipts as $receipt) {
            $target->receipts()->create($receipt->only([
                'reference_number', 'file_path', 'file_name', 'file_size', 'mime_type', 'uploaded_by',
            ]));
        }
    }
}
