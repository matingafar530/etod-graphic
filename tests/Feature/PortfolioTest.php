<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PortfolioItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    private function consOrderWithDesign(bool $consent = true): array
    {
        Storage::fake('local');
        Storage::disk('local')->put('customizations/originals/design.png', 'image-bytes');
        $category = Category::create(['name' => 'تست', 'slug' => 'portfolio-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'ماگ نمونه‌کار', 'slug' => 'portfolio-mug', 'status' => 'published', 'base_price' => 100000, 'printing_price' => 20000]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'PORTFOLIO-1', 'stock' => 5, 'reserved_stock' => 0, 'is_active' => true]);
        $order = Order::create(['session_id' => 'portfolio-session', 'access_token' => str_repeat('f', 40), 'order_number' => 'ORD-PORTFOLIO-0001', 'status' => 'completed', 'payment_status' => 'paid', 'customer_name' => 'مشتری', 'customer_phone' => '09123456789', 'customer_address' => 'تهران', 'total_price' => 120000, 'portfolio_consent' => $consent, 'consent_at' => $consent ? now() : null]);
        $item = $order->items()->create(['product_variant_id' => $variant->id, 'product_name' => 'ماگ نمونه‌کار', 'quantity' => 1, 'unit_price' => 120000, 'total_price' => 120000, 'preview_image_path' => 'customizations/originals/design.png']);

        return [$order, $item, $product];
    }

    public function test_checkout_persists_both_consents_and_audit_trail(): void
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'consent-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'تی‌شرت رضایت', 'slug' => 'consent-shirt', 'status' => 'published', 'base_price' => 100000, 'printing_price' => 10000]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'CONSENT-1', 'stock' => 3, 'reserved_stock' => 0, 'is_active' => true]);

        $request = Request::create('/checkout', 'POST', ['portfolio_consent' => '1', 'social_media_consent' => '1']);
        $request->setLaravelSession(app('session.store'));
        app(CartService::class)->add($request, $variant, 1);
        $order = app(OrderService::class)->createFromCart($request, [
            'customer_name' => 'کاربر تست', 'customer_phone' => '09123456789', 'customer_address' => 'تهران، خیابان تست',
            'portfolio_consent' => true, 'social_media_consent' => true,
        ]);

        $this->assertTrue($order->portfolio_consent);
        $this->assertNotNull($order->consent_at);
        $this->assertTrue($order->social_media_consent);
        $this->assertNotNull($order->social_consent_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'order.consent', 'auditable_id' => $order->id]);
    }

    public function test_admin_can_create_portfolio_item_from_consented_order(): void
    {
        [$order, $item] = $this->consOrderWithDesign();

        $response = $this->post(route('admin.portfolio.store'), ['order_item_id' => $item->id]);

        $response->assertRedirect()->assertSessionHas('success');
        $portfolio = PortfolioItem::firstOrFail();
        $this->assertFalse($portfolio->is_published);
        $this->assertSame($order->id, $portfolio->order_id);
        Storage::disk('local')->assertExists($portfolio->image_path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'portfolio.created']);
    }

    public function test_portfolio_creation_requires_consent(): void
    {
        [$order, $item] = $this->consOrderWithDesign(consent: false);

        $this->post(route('admin.portfolio.store'), ['order_item_id' => $item->id])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('portfolio_items', 0);
    }

    public function test_portfolio_item_is_limited_to_one_per_order_item(): void
    {
        [$order, $item] = $this->consOrderWithDesign();
        $this->post(route('admin.portfolio.store'), ['order_item_id' => $item->id])->assertRedirect();

        $this->post(route('admin.portfolio.store'), ['order_item_id' => $item->id])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('portfolio_items', 1);
    }

    public function test_published_portfolio_shows_on_home_and_image_route(): void
    {
        [$order, $item, $product] = $this->consOrderWithDesign();
        $portfolio = PortfolioItem::create(['order_id' => $order->id, 'order_item_id' => $item->id, 'product_id' => $product->id, 'title' => 'ماگ مشتری', 'image_path' => 'portfolio/published.png']);
        Storage::disk('local')->put($portfolio->image_path, 'published-image');
        $this->get(route('portfolio.image', $portfolio))->assertNotFound();

        $portfolio->update(['is_published' => true, 'published_at' => now()]);
        $this->get(route('portfolio.image', $portfolio))->assertOk();

        $this->get(route('home'))->assertOk()->assertSee('ماگ مشتری');
    }

    public function test_unpublished_portfolio_is_hidden_from_home(): void
    {
        [$order, $item] = $this->consOrderWithDesign();
        $portfolio = PortfolioItem::create(['order_id' => $order->id, 'order_item_id' => $item->id, 'title' => 'پیش‌نویس مخفی', 'image_path' => 'portfolio/draft.png']);
        Storage::disk('local')->put($portfolio->image_path, 'draft-image');

        $this->get(route('home'))->assertOk()->assertDontSee('پیش‌نویس مخفی');
    }
}
