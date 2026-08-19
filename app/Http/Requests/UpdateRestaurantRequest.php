<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRestaurantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('restaurant')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $restaurant = $this->route('restaurant');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            // 'slug' => [
            //     'required',
            //     'string',
            //     'max:255',
            //     'unique:restaurants,slug,' . $restaurant->id,
            // ],
            // Two slug validation wark are same
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('restaurants', 'slug') // slug will be uniqe but not this current restaurant slug so we will ignore this current restaurant id
                    ->ignore($restaurant->id),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'address' => [
                'required',
                'string',
                'max:500',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
            ],
        ];
    }
}
