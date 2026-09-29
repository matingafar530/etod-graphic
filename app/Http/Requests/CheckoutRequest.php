<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:2', 'max:120'],
            'customer_phone' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'customer_address' => ['required', 'string', 'min:10', 'max:1000'],
            'portfolio_consent' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'نام و نام خانوادگی را وارد کنید.',
            'customer_phone.required' => 'شماره موبایل را وارد کنید.',
            'customer_phone.regex' => 'شماره موبایل باید با ۰۹ شروع شود و ۱۱ رقم باشد.',
            'customer_address.required' => 'آدرس را وارد کنید.',
            'customer_address.min' => 'آدرس واردشده کوتاه است.',
        ];
    }
}
