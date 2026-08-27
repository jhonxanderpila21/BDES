<?php

namespace App\Http\Requests;

use App\Services\ReportedAssetService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportedAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'reported_date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }
}
