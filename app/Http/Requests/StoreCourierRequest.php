<?php

namespace App\Http\Requests;

use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourierRequest extends FormRequest
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
        return [
            'name'             => ['required', 'string', 'min:3', 'max:255'],
            'phone'            => ['required', 'string', 'max:20', 'unique:couriers,phone', 'regex:/^\+?[0-9\-\s]{7,20}$/'],
            'email'            => ['required', 'email:rfc', 'max:255', 'unique:couriers,email'],
            'nik'              => ['required', 'digits:16', 'unique:couriers,nik'],
            'address'          => ['nullable', 'string', 'max:500'],
            'city'             => ['nullable', 'string', 'max:100'],
            'vehicle_type'     => ['required', Rule::in(Courier::VEHICLE_TYPES)],
            'vehicle_plate'    => ['required', 'string', 'max:20', 'unique:couriers,vehicle_plate'],
            'level'            => ['required', 'integer', Rule::in(Courier::LEVELS)],
            'is_active'        => ['sometimes', 'boolean'],
            'rating'           => ['sometimes', 'numeric', 'min:0', 'max:5'],
            'total_deliveries' => ['sometimes', 'integer', 'min:0'],
            'registered_at'    => ['required', 'date', 'before_or_equal:today'],
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
