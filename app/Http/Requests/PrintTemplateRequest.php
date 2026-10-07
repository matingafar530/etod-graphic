<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class PrintTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'physical_width' => ['required', 'numeric', 'min:0.01', 'max:5000'],
            'physical_height' => ['required', 'numeric', 'min:0.01', 'max:5000'],
            'print_area_width' => ['required', 'numeric', 'min:0.01', 'max:5000'],
            'print_area_height' => ['required', 'numeric', 'min:0.01', 'max:5000'],
            'print_area_x' => ['required', 'numeric', 'min:0', 'max:5000'],
            'print_area_y' => ['required', 'numeric', 'min:0', 'max:5000'],
            'dpi' => ['required', 'integer', 'between:72,2400'],
            'allowed_formats' => ['required', 'array', 'min:1'],
            'allowed_formats.*' => ['required', 'in:jpg,jpeg,png,webp'],
            'max_image_size_kb' => ['required', 'integer', 'between:100,100000'],
            'is_active' => ['nullable', 'boolean'],
            'template_image' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'webp'])->max(10240)],
        ];
    }
}
