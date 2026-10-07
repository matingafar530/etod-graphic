<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrintTemplateRequest;
use App\Models\PrintTemplate;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPrintTemplateController extends Controller
{
    public function create(ProductVariant $variant): View
    {
        $this->ensureDevelopmentAdmin();
        abort_if($variant->printTemplate()->exists(), 409, 'برای این تنوع قالب چاپ از قبل ثبت شده است.');

        return view('admin.print-template-form', ['variant' => $variant->load('product', 'color', 'size'), 'template' => new PrintTemplate(['dpi' => 300, 'max_image_size_kb' => 10240, 'is_active' => true, 'allowed_formats' => ['jpg', 'jpeg', 'png', 'webp']]), 'creating' => true]);
    }

    public function store(PrintTemplateRequest $request, ProductVariant $variant): RedirectResponse
    {
        $this->ensureDevelopmentAdmin();
        abort_if($variant->printTemplate()->exists(), 409, 'برای این تنوع قالب چاپ از قبل ثبت شده است.');
        $data = $this->validatedData($request);
        $this->validatePrintArea($data);
        $file = $data['template_image'] ?? null;
        unset($data['template_image']);
        $path = $file?->store('print-templates/'.$variant->id, 'local');
        if ($path) {
            $data['template_image_path'] = $path;
        }
        $data['version'] = 1;
        $data['is_active'] = $request->boolean('is_active');

        try {
            DB::transaction(fn () => $variant->printTemplate()->create($data));
        } catch (\Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }

        return redirect()->route('admin.variants.edit', $variant)->with('success', 'قالب چاپ تنوع ثبت شد.');
    }

    public function edit(PrintTemplate $template): View
    {
        $this->ensureDevelopmentAdmin();

        return view('admin.print-template-form', ['template' => $template, 'variant' => $template->variant()->with('product', 'color', 'size')->firstOrFail(), 'creating' => false]);
    }

    public function update(PrintTemplateRequest $request, PrintTemplate $template): RedirectResponse
    {
        $this->ensureDevelopmentAdmin();
        $data = $this->validatedData($request);
        $this->validatePrintArea($data);
        $file = $data['template_image'] ?? null;
        unset($data['template_image']);
        $newPath = $file?->store('print-templates/'.$template->product_variant_id, 'local');
        if ($newPath) {
            $data['template_image_path'] = $newPath;
        }
        $data['is_active'] = $request->boolean('is_active');

        $specFields = ['physical_width', 'physical_height', 'print_area_width', 'print_area_height', 'print_area_x', 'print_area_y', 'dpi', 'allowed_formats', 'template_image_path'];
        $specChanged = false;
        foreach ($specFields as $field) {
            $next = $data[$field] ?? $template->{$field};
            $current = $template->{$field};
            if (in_array($field, ['physical_width', 'physical_height', 'print_area_width', 'print_area_height', 'print_area_x', 'print_area_y'], true)) {
                $specChanged = $specChanged || abs((float) $next - (float) $current) > 0.0001;
            } else {
                $specChanged = $specChanged || $next != $current;
            }
        }
        if ($specChanged) {
            $data['version'] = $template->version + 1;
        }

        try {
            DB::transaction(fn () => $template->update($data));
        } catch (\Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            throw $exception;
        }

        return redirect()->route('admin.variants.edit', $template->product_variant_id)->with('success', $specChanged ? 'قالب چاپ به‌روزرسانی شد و نسخهٔ آن افزایش یافت.' : 'قالب چاپ به‌روزرسانی شد.');
    }

    public function image(PrintTemplate $template): Response
    {
        $this->ensureDevelopmentAdmin();
        $path = $template->template_image_path;
        abort_unless($path && str_starts_with($path, 'print-templates/'), 404);
        abort_if(str_contains($path, '..') || ! Storage::disk('local')->exists($path), 404);
        $mime = Storage::disk('local')->mimeType($path);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return response(Storage::disk('local')->get($path), 200, ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, max-age=300']);
    }

    private function validatedData(PrintTemplateRequest $request): array
    {
        return $request->validated();
    }

    private function validatePrintArea(array $data): void
    {
        if ((float) $data['print_area_x'] + (float) $data['print_area_width'] > (float) $data['physical_width'] || (float) $data['print_area_y'] + (float) $data['print_area_height'] > (float) $data['physical_height']) {
            throw ValidationException::withMessages(['print_area_width' => 'محدودهٔ چاپ باید کاملاً داخل ابعاد فیزیکی محصول باشد.']);
        }
    }

    private function ensureDevelopmentAdmin(): void
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
    }
}
