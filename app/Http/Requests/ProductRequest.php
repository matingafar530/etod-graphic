<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'slug' => ['required', 'alpha_dash', 'max:180', Rule::unique('products', 'slug')->ignore($productId)],
            'description' => ['nullable', 'string', 'max:5000'],
            'base_price' => ['required', 'integer', 'min:0'],
            'printing_price' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in($this->route('product')?->status === 'archived' ? ['archived'] : ['draft', 'published'])],
            'is_customizable' => ['nullable', 'boolean'],
        ];
    }
}
