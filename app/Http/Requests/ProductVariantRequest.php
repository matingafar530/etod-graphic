<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    public function rules(): array
    {
        $variant = $this->route('variant');
        $rules = [
            'color_id' => ['nullable', 'integer', 'exists:colors,id'],
            'size_id' => ['nullable', 'integer', 'exists:sizes,id'],
            'model' => ['nullable', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('product_variants', 'sku')->ignore($variant?->id)],
            'price' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'printing_price' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($this->isMethod('post')) {
            $rules['initial_stock'] = ['nullable', 'integer', 'min:0', 'max:100000'];
            $rules['initial_stock_reason'] = ['nullable', 'string', 'min:3', 'max:255'];
        }

        return $rules;
    }
}
