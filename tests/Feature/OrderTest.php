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
use Illuminate\Support\Facades\Storage;
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

    public function test_order_receives_access_token_on_creation(): void
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'token-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'ماگ تست', 'slug' => 'token-mug', 'status' => 'published']);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'TOKEN-1', 'stock' => 3, 'reserved_stock' => 0, 'is_active' => true]);

        $request = Request::create('/checkout', 'POST');
        $request->setLaravelSession(app('session.store'));
        app(CartService::class)->add($request, $variant, 1);
        $order = app(OrderService::class)->createFromCart($request, ['customer_name' => 'کاربر تست', 'customer_phone' => '09123456789', 'customer_address' => 'تهران']);

        $this->assertNotEmpty($order->access_token);
        $this->assertSame(40, mb_strlen($order->access_token));
    }

    public function test_guest_can_track_order_by_token(): void
    {
        $order = Order::create(['session_id' => 'original-session', 'access_token' => str_repeat('t', 40), 'order_number' => 'ORD-TRACK-0001', 'status' => 'pending_payment', 'payment_status' => 'pending', 'customer_name' => 'مهمان', 'customer_phone' => '09123456789', 'customer_address' => 'تهران', 'total_price' => 90000]);
        $order->items()->create(['product_variant_id' => null, 'product_name' => 'تی‌شرت', 'quantity' => 1, 'unit_price' => 90000, 'total_price' => 90000]);

        $this->get(route('orders.track', ['token' => $order->access_token]))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('تی‌شرت');
    }

    public function test_unknown_token_is_rejected(): void
    {
        $this->get(route('orders.track', ['token' => str_repeat('x', 40)]))->assertNotFound();
    }

    public function test_mock_payment_is_authorized_by_token(): void
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'payment-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'ماگ تست', 'slug' => 'payment-mug', 'status' => 'published']);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'PAYMENT-1', 'stock' => 5, 'reserved_stock' => 1, 'is_active' => true]);
        $order = Order::create(['session_id' => 'old-session', 'access_token' => str_repeat('p', 40), 'order_number' => 'ORD-2026-000001', 'status' => 'pending_payment', 'payment_status' => 'pending', 'customer_name' => 'تست', 'customer_phone' => '09123456789', 'customer_address' => 'آدرس تست', 'total_price' => 50000]);
        $order->items()->create(['product_variant_id' => $variant->id, 'product_name' => 'ماگ تست', 'quantity' => 1, 'unit_price' => 50000, 'total_price' => 50000]);

        $this->post(route('orders.mock-payment', $order), ['token' => $order->access_token])->assertRedirect();

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(1, $order->fresh()->payments()->count());
        $this->assertSame(0, $variant->fresh()->reserved_stock);
    }

    public function test_mock_payment_rejects_wrong_token(): void
    {
        $order = Order::create(['session_id' => 'some-session', 'access_token' => str_repeat('q', 40), 'order_number' => 'ORD-2026-000002', 'status' => 'pending_payment', 'payment_status' => 'pending', 'customer_name' => 'تست', 'customer_phone' => '09123456789', 'customer_address' => 'آدرس تست', 'total_price' => 50000]);

        $this->post(route('orders.mock-payment', $order), ['token' => 'wrong-token'])->assertNotFound();
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_design_file_is_served_through_token_route(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('customizations/originals/design.png', 'binary-image-data');
        $order = Order::create(['session_id' => 'original-session', 'access_token' => str_repeat('d', 40), 'order_number' => 'ORD-DESIGN-0001', 'status' => 'paid', 'payment_status' => 'paid', 'customer_name' => 'مهمان', 'customer_phone' => '09123456789', 'customer_address' => 'تهران', 'total_price' => 90000]);
        $item = $order->items()->create(['product_variant_id' => null, 'product_name' => 'تی‌شرت', 'quantity' => 1, 'unit_price' => 90000, 'total_price' => 90000, 'preview_image_path' => 'customizations/originals/design.png']);

        $this->get(route('orders.track.design', ['token' => $order->access_token, 'item' => $item]))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_design_route_rejects_item_from_other_order(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('customizations/originals/other.png', 'data');
        $order = Order::create(['session_id' => 's1', 'access_token' => str_repeat('a', 40), 'order_number' => 'ORD-DESIGN-0002', 'status' => 'paid', 'payment_status' => 'paid', 'customer_name' => 'الف', 'customer_phone' => '09123456789', 'customer_address' => 'تهران', 'total_price' => 10000]);
        $otherOrder = Order::create(['session_id' => 's2', 'access_token' => str_repeat('b', 40), 'order_number' => 'ORD-DESIGN-0003', 'status' => 'paid', 'payment_status' => 'paid', 'customer_name' => 'ب', 'customer_phone' => '09123456789', 'customer_address' => 'تهران', 'total_price' => 10000]);
        $item = $otherOrder->items()->create(['product_variant_id' => null, 'product_name' => 'ماگ', 'quantity' => 1, 'unit_price' => 10000, 'total_price' => 10000, 'preview_image_path' => 'customizations/originals/other.png']);

        $this->get(route('orders.track.design', ['token' => $order->access_token, 'item' => $item]))->assertNotFound();
    }

    public function test_checkout_recalculates_unit_price_from_current_variant_price(): void
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'reprice-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'کوسن تست', 'slug' => 'reprice-cushion', 'status' => 'published', 'base_price' => 200000, 'printing_price' => 50000]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'REPRICE-1', 'stock' => 3, 'reserved_stock' => 0, 'is_active' => true]);

        $request = Request::create('/checkout', 'POST');
        $request->setLaravelSession(app('session.store'));
        app(CartService::class)->add($request, $variant, 1);
        // Simulate a price change after the item was added to the cart.
        $variant->update(['price' => 300000]);

        $order = app(OrderService::class)->createFromCart($request, ['customer_name' => 'کاربر تست', 'customer_phone' => '09123456789', 'customer_address' => 'تهران']);

        $this->assertSame(350000, $order->items->first()->unit_price);
        $this->assertSame(350000, $order->total_price);
    }

    public function test_mock_payment_is_idempotent_and_releases_reserved_stock(): void
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'payment-idem-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'ماگ تست', 'slug' => 'payment-mug-2', 'status' => 'published']);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'PAYMENT-2', 'stock' => 5, 'reserved_stock' => 1, 'is_active' => true]);
        $order = Order::create(['session_id' => session()->getId(), 'order_number' => 'ORD-2026-000009', 'status' => 'pending_payment', 'payment_status' => 'pending', 'customer_name' => 'تست', 'customer_phone' => '09123456789', 'customer_address' => 'آدرس تست', 'total_price' => 50000]);
        $order->items()->create(['product_variant_id' => $variant->id, 'product_name' => 'ماگ تست', 'quantity' => 1, 'unit_price' => 50000, 'total_price' => 50000]);

        app(OrderService::class)->mockPay($order);
        app(OrderService::class)->mockPay($order);

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(1, $order->fresh()->payments()->count());
        $this->assertSame(0, $variant->fresh()->reserved_stock);
    }
}
