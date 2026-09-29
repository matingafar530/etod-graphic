<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $value = $this->input('customization_data');
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->merge(['customization_data' => is_array($decoded) ? $decoded : null]);
        }
    }

    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'upload_id' => ['nullable', 'integer', 'exists:uploads,id'],
            'customization_data' => ['nullable', 'array'],
            'customization_data.x' => ['nullable', 'numeric', 'between:0,100'],
            'customization_data.y' => ['nullable', 'numeric', 'between:0,100'],
            'customization_data.width' => ['nullable', 'numeric', 'between:1,100'],
            'customization_data.height' => ['nullable', 'numeric', 'between:1,100'],
            'customization_data.rotation' => ['nullable', 'numeric', 'between:-360,360'],
        ];
    }

    public function messages(): array
    {
        return ['variant_id.required' => 'لطفاً گزینه محصول را انتخاب کنید.', 'quantity.max' => 'حداکثر تعداد قابل سفارش ۵۰ عدد است.'];
    }
}
