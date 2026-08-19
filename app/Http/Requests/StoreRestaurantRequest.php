<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return $this->user()->hasRole('restaurant_owner');
    // }
    public function authorize(): bool
    {
        return $this->user()?->can('create restaurants') ?? false;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:restaurants,slug',
            ],

            'description' => ['nullable', 'string'],

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
