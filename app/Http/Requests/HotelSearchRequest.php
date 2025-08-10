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
     */
    public function rules(): array
    {
        return [
            'location'   => 'required|string|min:2',
            'check_in'   => 'required|date|after_or_equal:today',
            'check_out'  => 'required|date|after:check_in',
            'guests'     => 'nullable|integer|min:1|max:10',
            'min_price'  => 'nullable|numeric|min:0',
            'max_price'  => 'nullable|numeric|gte:min_price',
            'sort_by'    => 'nullable|in:pricePerNight,rating',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'location.required'       => __('validation.location.required'),
            'location.string'         => __('validation.location.string'),
            'location.min'            => __('validation.location.min'),

            'check_in.required'       => __('validation.check_in.required'),
            'check_in.date'           => __('validation.check_in.date'),
            'check_in.after_or_equal' => __('validation.check_in.after_or_equal'),

            'check_out.required'      => __('validation.check_out.required'),
            'check_out.date'          => __('validation.check_out.date'),
            'check_out.after'         => __('validation.check_out.after'),

            'guests.integer'          => __('validation.guests.integer'),
            'guests.min'              => __('validation.guests.min'),
            'guests.max'              => __('validation.guests.max'),

            'min_price.numeric'       => __('validation.min_price.numeric'),
            'min_price.min'           => __('validation.min_price.min'),

            'max_price.numeric'       => __('validation.max_price.numeric'),
            'max_price.gte'           => __('validation.max_price.gte'),

            'sort_by.in'              => __('validation.sort_by.in'),
        ];
    }
}
