<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $input = $this->all();

        if (empty($input['first_name']) && !empty($input['name'])) {
            $parts = explode(' ', trim($input['name']));
            if (count($parts) === 1) {
                $input['first_name'] = $parts[0];
                $input['last_name'] = $parts[0];
            } else {
                $input['last_name'] = array_pop($parts);
                $input['first_name'] = implode(' ', $parts);
            }
        }

        foreach (['first_name', 'middle_name', 'last_name', 'suffix'] as $f) {
            if (isset($input[$f])) {
                $input[$f] = strip_tags(trim($input[$f]));
            }
        }

        $input['name'] = strip_tags(trim($input['name'] ?? ''));
        $input['username'] = !empty($input['username']) ? strtolower(trim(strip_tags($input['username']))) : null;
        $input['email'] = strtolower(trim($input['email'] ?? ''));

        $this->merge($input);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $nameRegex = '/^[a-zA-Z\s\-\'\.ñÑáéíóúÁÉÍÓÚ]+$/u';

        return [
            'first_name' => ['nullable', 'string', 'max:50', 'regex:' . $nameRegex],
            'middle_name' => ['nullable', 'string', 'max:50', 'regex:' . $nameRegex],
            'last_name' => ['nullable', 'string', 'max:50', 'regex:' . $nameRegex],
            'suffix' => ['nullable', 'string', 'max:20'],
            'name' => ['nullable', 'string', 'max:255'],
            'username' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash:ascii',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
