<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_adjustment_records_movement_and_rejects_below_reserved(): void
    {
        $category = Category::create(['name' => 'عملیات', 'slug' => 'operations', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'محصول عملیات', 'slug' => 'operations-product', 'status' => 'published']);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'OPS-1', 'stock' => 10, 'reserved_stock' => 3, 'is_active' => true]);

        app(InventoryService::class)->adjust($variant, 5, 'ورود تستی');
        $this->assertDatabaseHas('inventory_movements', ['product_variant_id' => $variant->id, 'quantity' => 5, 'stock_after' => 15]);

        $this->expectException(ValidationException::class);
        app(InventoryService::class)->adjust($variant->fresh(), -13, 'خروج نامعتبر');
    }

    public function test_paid_order_creates_print_job_once(): void
    {
        $order = Order::create(['session_id' => 'print-session', 'order_number' => 'ORD-PRINT-0001', 'status' => 'pending_payment', 'payment_status' => 'pending', 'customer_name' => 'چاپ', 'customer_phone' => '09123456789', 'customer_address' => 'تهران', 'currency' => 'IRR']);
        $order->items()->create(['product_name' => 'محصول چاپ', 'quantity' => 1, 'unit_price' => 100, 'printing_price' => 0, 'total_price' => 100]);

        app(OrderService::class)->mockPay($order);
        app(OrderService::class)->mockPay($order->fresh());

        $this->assertDatabaseCount('print_jobs', 1);
        $this->assertDatabaseHas('print_jobs', ['order_id' => $order->id, 'status' => 'queued']);
    }
}
