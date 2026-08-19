<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // $category = $this->route('category');

        // return $this->user()?->can('update categories')
        //     && $this->user()->id === $category->restaurant->user_id;
        $category = $this->route('category');

        return $this->user()?->can('update', $category) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',

                Rule::unique('categories', 'slug')
                    ->where(
                        fn($query) => $query->where(
                            'restaurant_id',
                            $category->restaurant_id
                        )
                    )
                    ->ignore($category->id),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }
}
