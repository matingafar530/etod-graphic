<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductVariantRequest;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminProductVariantController extends Controller
{
    public function create(Product $product): View
    {
        $this->ensureDevelopmentAdmin();

        return view('admin.variant-form', [
            'product' => $product,
            'variant' => new ProductVariant(['is_active' => true]),
            'colors' => Color::query()->orderBy('name')->get(),
            'sizes' => Size::query()->orderBy('name')->get(),
            'creating' => true,
        ]);
    }

    public function store(ProductVariantRequest $request, Product $product, InventoryService $inventory): RedirectResponse
    {
        $data = $request->validated();
        $initialStock = (int) ($data['initial_stock'] ?? 0);
        $reason = trim((string) ($data['initial_stock_reason'] ?? ''));
        unset($data['initial_stock'], $data['initial_stock_reason']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($initialStock > 0 && mb_strlen($reason) < 3) {
            return back()->withErrors(['initial_stock_reason' => 'برای ثبت موجودی اولیه، دلیل حداقل سه‌حرفی وارد کنید.'])->withInput();
        }

        $variant = DB::transaction(function () use ($product, $data, $initialStock, $reason, $inventory) {
            $variant = $product->variants()->create($data + ['stock' => 0, 'reserved_stock' => 0]);
            if ($initialStock > 0) $inventory->adjust($variant, $initialStock, $reason);

            return $variant;
        });

        return redirect()->route('admin.variants.edit', $variant)->with('success', 'تنوع محصول ثبت شد. موجودی اولیه در دفتر گردش انبار ثبت شد.');
    }

    public function edit(ProductVariant $variant): View
    {
        $this->ensureDevelopmentAdmin();

        return view('admin.variant-form', [
            'product' => $variant->product,
            'variant' => $variant->load('printTemplate'),
            'colors' => Color::query()->orderBy('name')->get(),
            'sizes' => Size::query()->orderBy('name')->get(),
            'creating' => false,
        ]);
    }

    public function update(ProductVariantRequest $request, ProductVariant $variant): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $variant->update($data);

        return back()->with('success', 'تنوع محصول به‌روزرسانی شد. موجودی را از بخش انبار تغییر دهید تا گردش موجودی ثبت بماند.');
    }

    private function ensureDevelopmentAdmin(): void
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
    }
}
