<?php

namespace App\Http\Requests;

use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $courierId = $this->route('courier')?->id ?? $this->route('courier');

        return [
            'name'             => ['sometimes', 'string', 'min:3', 'max:255'],
            'phone'            => ['sometimes', 'string', 'max:20', Rule::unique('couriers', 'phone')->ignore($courierId), 'regex:/^\+?[0-9\-\s]{7,20}$/'],
            'email'            => ['sometimes', 'email:rfc', 'max:255', Rule::unique('couriers', 'email')->ignore($courierId)],
            'nik'              => ['sometimes', 'digits:16', Rule::unique('couriers', 'nik')->ignore($courierId)],
            'address'          => ['nullable', 'string', 'max:500'],
            'city'             => ['nullable', 'string', 'max:100'],
            'vehicle_type'     => ['sometimes', Rule::in(Courier::VEHICLE_TYPES)],
            'vehicle_plate'    => ['sometimes', 'string', 'max:20', Rule::unique('couriers', 'vehicle_plate')->ignore($courierId)],
            'level'            => ['sometimes', 'integer', Rule::in(Courier::LEVELS)],
            'is_active'        => ['sometimes', 'boolean'],
            'rating'           => ['sometimes', 'numeric', 'min:0', 'max:5'],
            'total_deliveries' => ['sometimes', 'integer', 'min:0'],
            'registered_at'    => ['sometimes', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * Custom human-readable attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nik'           => 'NIK',
            'vehicle_type'  => 'vehicle type',
            'vehicle_plate' => 'vehicle plate',
            'registered_at' => 'registration date',
        ];
    }
}
