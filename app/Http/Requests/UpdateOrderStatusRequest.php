<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    public function rules(): array
    {
        return ['status' => ['required', 'string', 'in:draft,pending_payment,paid,processing,printing,ready,shipped,completed,cancelled,payment_failed']];
    }

    public function messages(): array
    {
        return ['status.required' => 'وضعیت جدید را انتخاب کنید.', 'status.in' => 'وضعیت انتخاب‌شده معتبر نیست.'];
    }
}
