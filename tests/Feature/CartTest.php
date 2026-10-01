<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_a_variant_and_backend_calculates_price(): void
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'test', 'is_active' => true]);
        $color = Color::create(['name' => 'سفید', 'slug' => 'white', 'hex_code' => '#FFFFFF']);
        $size = Size::create(['name' => 'M', 'slug' => 'm']);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'محصول تست', 'slug' => 'test-product',
            'status' => 'published', 'base_price' => 100000, 'printing_price' => 25000,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'color_id' => $color->id, 'size_id' => $size->id,
            'sku' => 'TEST-WHITE-M', 'price' => 120000, 'printing_price' => 30000,
            'stock' => 5, 'reserved_stock' => 1, 'is_active' => true,
        ]);

        $response = $this->post(route('cart.items.store'), [
            'variant_id' => $variant->id, 'quantity' => 2,
            'customization_data' => ['x' => 50, 'y' => 50, 'width' => 40, 'height' => 40],
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id, 'quantity' => 2, 'unit_price' => 150000,
        ]);
    }

    public function test_cart_accepts_request_without_customization_data(): void
    {
        $category = Category::create(['name' => 'تست بدون تصویر', 'slug' => 'no-customization-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'محصول بدون تصویر', 'slug' => 'no-customization-product', 'status' => 'published', 'base_price' => 100000, 'printing_price' => 0]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'NO-CUSTOMIZATION-1', 'price' => 100000, 'printing_price' => 0, 'stock' => 2, 'reserved_stock' => 0, 'is_active' => true]);

        $this->post(route('cart.items.store'), ['variant_id' => $variant->id, 'quantity' => 1])->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $variant->id, 'quantity' => 1, 'customization_data' => null]);
    }

    public function test_cart_rejects_quantity_above_available_stock(): void
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'stock-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'محصول موجودی', 'slug' => 'stock-product', 'status' => 'published']);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'STOCK-1', 'stock' => 2, 'reserved_stock' => 1, 'is_active' => true]);

        $response = $this->from('/products/stock-product')->post(route('cart.items.store'), [
            'variant_id' => $variant->id, 'quantity' => 2,
        ]);

        $response->assertRedirect('/products/stock-product');
        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('cart_items', 0);
    }
}
