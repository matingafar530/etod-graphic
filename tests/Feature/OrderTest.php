<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_order_snapshot_and_reserves_stock(): void
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'order-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'تی‌شرت تست', 'slug' => 'order-shirt', 'status' => 'published', 'base_price' => 100000, 'printing_price' => 20000]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'ORDER-1', 'stock' => 5, 'reserved_stock' => 0, 'is_active' => true]);

        $request = Request::create('/checkout', 'POST');
        $request->setLaravelSession(app('session.store'));
        app(CartService::class)->add($request, $variant, 2);
        $order = app(OrderService::class)->createFromCart($request, ['customer_name' => 'کاربر تست', 'customer_phone' => '09123456789', 'customer_address' => 'تهران، خیابان تست، پلاک ۱']);

        $this->assertSame('pending_payment', $order->status);
        $this->assertSame(240000, $order->total_price);
        $this->assertSame('تی‌شرت تست', $order->items->first()->product_name);
        $this->assertSame(2, $variant->fresh()->reserved_stock);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_mock_payment_is_idempotent_and_releases_reserved_stock(): void
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'payment-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'ماگ تست', 'slug' => 'payment-mug', 'status' => 'published']);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'PAYMENT-1', 'stock' => 5, 'reserved_stock' => 1, 'is_active' => true]);
        $order = Order::create(['session_id' => session()->getId(), 'order_number' => 'ORD-2026-000001', 'status' => 'pending_payment', 'payment_status' => 'pending', 'customer_name' => 'تست', 'customer_phone' => '09123456789', 'customer_address' => 'آدرس تست', 'total_price' => 50000]);
        $order->items()->create(['product_variant_id' => $variant->id, 'product_name' => 'ماگ تست', 'quantity' => 1, 'unit_price' => 50000, 'total_price' => 50000]);

        app(OrderService::class)->mockPay($order);
        app(OrderService::class)->mockPay($order);

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(1, $order->fresh()->payments()->count());
        $this->assertSame(0, $variant->fresh()->reserved_stock);
    }
}
