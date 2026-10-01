<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_admin_pages_are_available(): void
    {
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('داشبورد مدیریت');
        $this->get(route('admin.orders'))->assertOk()->assertSee('مدیریت سفارش‌ها');
    }

    public function test_invalid_order_transition_is_rejected(): void
    {
        $order = Order::create([
            'session_id' => 'admin-test-session', 'order_number' => 'ORD-TEST-0001', 'status' => 'printing', 'payment_status' => 'paid',
            'customer_name' => 'تست', 'customer_phone' => '09123456789', 'customer_address' => 'تهران', 'subtotal' => 0,
            'printing_total' => 0, 'shipping_price' => 0, 'discount' => 0, 'total_price' => 0, 'currency' => 'IRR',
        ]);

        $this->expectException(ValidationException::class);
        app(OrderStatusService::class)->change($order, 'completed');
    }

    public function test_admin_can_change_only_allowed_order_status(): void
    {
        $order = Order::create([
            'session_id' => 'admin-test-session-2', 'order_number' => 'ORD-TEST-0002', 'status' => 'paid', 'payment_status' => 'paid',
            'customer_name' => 'تست', 'customer_phone' => '09123456789', 'customer_address' => 'تهران', 'subtotal' => 0,
            'printing_total' => 0, 'shipping_price' => 0, 'discount' => 0, 'total_price' => 0, 'currency' => 'IRR',
        ]);

        $this->patch(route('admin.orders.status', $order), ['status' => 'processing'])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'processing']);
    }
}
