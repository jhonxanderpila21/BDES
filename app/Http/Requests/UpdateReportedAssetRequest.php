<?php

namespace App\Http\Requests;

use App\Services\ReportedAssetService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportedAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'location'      => strip_tags(trim($this->location ?? '')),
            'description'   => strip_tags(trim($this->description ?? '')),
            'action'        => strip_tags(trim($this->action ?? '')),
            'reported_date' => $this->filled('reported_date') ? $this->reported_date : ($this->route('reported_asset')?->reported_date?->format('Y-m-d') ?? now()->format('Y-m-d')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'asset_id'      => ['required', 'exists:assets,id'],
            'reported_type' => ['required', Rule::in(ReportedAssetService::reportedTypes())],
            'location'      => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'action'        => ['nullable', 'string', 'max:255'],
            'reported_date' => ['nullable', 'date', 'before_or_equal:today'],
            'resolved_date' => ['nullable', 'date', 'after_or_equal:reported_date'],
        ];
    }
}
