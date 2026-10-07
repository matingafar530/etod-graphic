<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        $data = $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'alt_text' => ['nullable', 'string', 'max:180'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
        ]);
        if (! empty($data['product_variant_id'])) {
            abort_unless($product->variants()->whereKey($data['product_variant_id'])->exists(), 422);
        }
        $path = $request->file('image')->store('product-images', 'local');
        if ($request->boolean('is_primary')) {
            $scope = $product->images();
            ! empty($data['product_variant_id']) ? $scope->where('product_variant_id', $data['product_variant_id']) : $scope->whereNull('product_variant_id');
            $scope->update(['is_primary' => false]);
        }
        $product->images()->create(['product_variant_id' => $data['product_variant_id'] ?? null, 'disk' => 'local', 'path' => $path, 'alt_text' => $data['alt_text'] ?? $product->name, 'is_primary' => $request->boolean('is_primary'), 'sort_order' => (int) $product->images()->max('sort_order') + 1]);

        return back()->with('success', 'تصویر محصول اضافه شد.');
    }

    public function show(ProductImage $image): Response
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        abort_unless(Storage::disk($image->disk)->exists($image->path), 404);

        return response(Storage::disk($image->disk)->get($image->path), 200, ['Content-Type' => Storage::disk($image->disk)->mimeType($image->path), 'Cache-Control' => 'private, max-age=3600']);
    }

    public function destroy(ProductImage $image): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        Storage::disk($image->disk)->delete($image->path);
        $image->delete();

        return back()->with('success', 'تصویر محصول حذف شد.');
    }
}
