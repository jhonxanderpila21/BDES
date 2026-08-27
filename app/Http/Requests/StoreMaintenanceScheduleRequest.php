<?php

namespace App\Http\Requests;

use App\Services\MaintenanceScheduleService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceScheduleRequest extends FormRequest
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
            'asset_id' => ['required', 'exists:assets,id'],
            'maintenance_task' => ['required', 'string', 'max:255'],
            'interval_type' => ['required', Rule::in(MaintenanceScheduleService::intervalTypes())],
            'interval_days' => ['required', 'integer', 'min:1'],
            'last_maintenance_date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }
}
