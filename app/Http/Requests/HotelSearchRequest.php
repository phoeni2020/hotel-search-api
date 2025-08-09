<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelSearchRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location'   => 'required|string',
            'check_in'   => 'required|date|after_or_equal:today',
            'check_out'  => 'required|date|after:check_in',
            'guests'     => 'nullable|integer|min:1',
            'min_price'  => 'nullable|numeric|min:0',
            'max_price'  => 'nullable|numeric|gte:min_price',
            'sort_by'    => 'nullable|in:pricePerNight,rating',
        ];
    }

    public function messages(): array
    {
        return [
            'location.required'   => __(''),
            'location.string'   => 'required|string',
            'check_in.required'   => 'required|date|after_or_equal:today',
            'check_in.date'   => 'required|date|after_or_equal:today',
            'check_in.after_or_equal'   => 'required|date|after_or_equal:today',
            'check_out.required'   => 'required|date|after_or_equal:today',
            'check_out.date'   => 'required|date|after_or_equal:today',
            'check_out.after'   => 'required|date|after_or_equal:today',
            'check_out'  => 'required|date|after:check_in',
            'guests.integer'     => 'nullable|integer|min:1',
            'guests.min'     => 'nullable|integer|min:1',
            'min_price.numeric'  => 'nullable|numeric|min:0',
            'min_price.min'  => 'nullable|numeric|min:0',
            'max_price.numeric'  => 'nullable|numeric|gte:min_price',
            'max_price.gte'  => 'nullable|numeric|gte:min_price',
        ];
    }
}
