<?php

namespace Tests\Feature;

use App\Events\OrderPaid;
use App\Events\OrderReady;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function variant(): ProductVariant
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'notif-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'ماگ اعلان', 'slug' => 'notif-mug', 'status' => 'published', 'base_price' => 100000, 'printing_price' => 20000]);

        return ProductVariant::create(['product_id' => $product->id, 'sku' => 'NOTIF-1', 'stock' => 5, 'reserved_stock' => 0, 'is_active' => true]);
    }

    private function checkout(ProductVariant $variant, int $quantity = 1): Order
    {
        $request = Request::create('/checkout', 'POST');
        $request->setLaravelSession(app('session.store'));
        app(CartService::class)->add($request, $variant, $quantity);

        return app(OrderService::class)->createFromCart($request, ['customer_name' => 'کاربر تست', 'customer_phone' => '09123456789', 'customer_address' => 'تهران، خیابان تست']);
    }

    public function test_checkout_logs_created_notifications_for_both_channels(): void
    {
        $this->checkout($this->variant());

        foreach (['sms', 'whatsapp'] as $channel) {
            $this->assertDatabaseHas('notification_logs', ['channel' => $channel, 'event' => 'order.created', 'recipient' => '09123456789', 'status' => 'sent']);
        }
    }

    public function test_mock_pay_logs_paid_notifications_once(): void
    {
        $order = $this->checkout($this->variant());
        $before = DB::table('notification_logs')->count();

        app(OrderService::class)->mockPay($order);
        app(OrderService::class)->mockPay($order);

        $paid = DB::table('notification_logs')->where('event', 'order.paid')->count();
        $this->assertSame(2, $paid);
        $this->assertSame($before + 2, DB::table('notification_logs')->count());
    }

    public function test_ready_status_logs_ready_notifications(): void
    {
        $order = $this->checkout($this->variant());
        $order->update(['status' => 'paid', 'payment_status' => 'paid']);
        app(OrderStatusService::class)->change($order, 'processing');
        app(OrderStatusService::class)->change($order, 'printing');
        app(OrderStatusService::class)->change($order, 'ready');

        foreach (['sms', 'whatsapp'] as $channel) {
            $this->assertDatabaseHas('notification_logs', ['channel' => $channel, 'event' => 'order.ready', 'order_id' => $order->id]);
        }
    }

    public function test_events_dispatch_without_notifications_for_missing_phone(): void
    {
        $order = $this->checkout($this->variant());
        $order->update(['customer_phone' => null]);

        OrderPaid::dispatch($order);
        OrderReady::dispatch($order);

        $this->assertDatabaseMissing('notification_logs', ['event' => 'order.paid']);
        $this->assertDatabaseMissing('notification_logs', ['event' => 'order.ready']);
    }

    public function test_admin_notifications_page_renders(): void
    {
        $this->checkout($this->variant());

        $this->get(route('admin.notifications'))
            ->assertOk()
            ->assertSee('لاگ اعلان‌ها')
            ->assertSee('09123456789');
    }
}
