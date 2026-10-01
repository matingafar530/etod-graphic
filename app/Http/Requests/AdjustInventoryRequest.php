<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'between:-100000,100000', 'not_in:0'],
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return ['quantity.required' => 'مقدار تغییر موجودی را وارد کنید.', 'quantity.not_in' => 'مقدار تغییر موجودی نمی‌تواند صفر باشد.', 'reason.required' => 'دلیل تغییر موجودی را وارد کنید.'];
    }
}
