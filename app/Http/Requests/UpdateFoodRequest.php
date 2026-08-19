<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFoodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $food = $this->route('food');

        return $this->user()?->can('update foods')
            && $this->user()->id === $food->restaurant->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $food = $this->route('food');

        return [
            'category_id' => [
                'nullable',
                'integer',

                Rule::exists('categories', 'id')
                    ->where(
                        fn($query) => $query->where(
                            'restaurant_id',
                            $food->restaurant_id
                        )
                    ),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',

                Rule::unique('foods', 'slug')
                    ->where(
                        fn($query) => $query->where(
                            'restaurant_id',
                            $food->restaurant_id
                        )
                    )
                    ->ignore($food->id),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'is_available' => [
                'boolean',
            ],
        ];
    }
}
