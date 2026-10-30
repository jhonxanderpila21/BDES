<?php

namespace App\Http\Requests;

use App\Services\AssetNeedStatusService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetNeedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->user() && ($this->user()->isViewer() || $this->user()->hasRole('viewer', 'staff'))) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return array_merge([
            'asset_id'         => ['required', 'exists:assets,id'],
            'requirement'      => ['required', 'string', 'max:255'],
            'unit'             => ['required', Rule::in(AssetNeedStatusService::units())],
            'maximum_capacity' => ['required', 'numeric', 'min:0.01'],
            'minimum_level'    => ['required', 'numeric', 'min:0', 'lt:maximum_capacity'],
            'current_level'    => ['required', 'numeric', 'min:0', 'lte:maximum_capacity'],
        ], \App\Services\ReceiptService::rules());
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge([
            'minimum_level.lt'  => 'The minimum level must be less than the maximum capacity.',
            'current_level.lte' => 'The current level cannot exceed the maximum capacity.',
        ], \App\Services\ReceiptService::messages());
    }
}
