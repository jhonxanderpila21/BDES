<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetAccessoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user && ($user->isViewer() || $user->hasRole('viewer', 'staff'))) {
            return false;
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        $unitCost = $this->unit_cost;
        if (is_string($unitCost)) {
            $unitCost = trim(str_replace([',', '₱', '$', 'PHP', ' '], '', $unitCost));
        }

        $this->merge([
            'accessory_name' => strip_tags(trim($this->accessory_name ?? '')),
            'unit_cost'      => $unitCost,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return array_merge([
            'asset_id' => ['required', 'exists:assets,id'],
            'accessory_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
        ], \App\Services\ReceiptService::rules());
    }

    public function messages(): array
    {
        return \App\Services\ReceiptService::messages();
    }
}
