<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function completedOrderItem(): array
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'review-test', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'ماگ نظر', 'slug' => 'review-mug', 'status' => 'published', 'base_price' => 180000, 'printing_price' => 60000]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'REVIEW-1', 'stock' => 5, 'reserved_stock' => 0, 'is_active' => true]);
        $order = Order::create(['session_id' => 'reviewer-session', 'access_token' => str_repeat('r', 40), 'order_number' => 'ORD-REVIEW-0001', 'status' => 'completed', 'payment_status' => 'paid', 'customer_name' => 'خریدار', 'customer_phone' => '09123456789', 'customer_address' => 'تهران', 'total_price' => 240000]);
        $item = $order->items()->create(['product_variant_id' => $variant->id, 'product_name' => 'ماگ نظر', 'quantity' => 1, 'unit_price' => 240000, 'total_price' => 240000]);

        return [$order, $item, $product];
    }

    public function test_completed_order_item_can_be_reviewed_via_token(): void
    {
        [$order, $item, $product] = $this->completedOrderItem();

        $response = $this->post(route('reviews.store', ['token' => $order->access_token, 'item' => $item]), [
            'rating' => 5,
            'author_name' => 'خریدار',
            'body' => 'کیفیت چاپ عالی بود.',
        ]);

        $response->assertRedirect(route('orders.track', ['token' => $order->access_token]))->assertSessionHas('success');
        $review = Review::firstOrFail();
        $this->assertSame('pending', $review->status);
        $this->assertSame($product->id, $review->product_id);
        $this->assertSame($item->id, $review->order_item_id);
        $this->assertNull($review->reviewed_at);
    }

    public function test_review_requires_completed_order(): void
    {
        [$order, $item] = $this->completedOrderItem();
        $order->update(['status' => 'paid']);

        $this->post(route('reviews.store', ['token' => $order->access_token, 'item' => $item]), [
            'rating' => 4, 'author_name' => 'خریدار',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_review_is_limited_to_one_per_order_item(): void
    {
        [$order, $item] = $this->completedOrderItem();
        $this->post(route('reviews.store', ['token' => $order->access_token, 'item' => $item]), ['rating' => 5, 'author_name' => 'خریدار'])->assertRedirect();

        $this->post(route('reviews.store', ['token' => $order->access_token, 'item' => $item]), ['rating' => 2, 'author_name' => 'خریدار'])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        [$order, $item] = $this->completedOrderItem();

        $this->from('/test')->post(route('reviews.store', ['token' => $order->access_token, 'item' => $item]), [
            'rating' => 6, 'author_name' => 'خریدار',
        ])->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_moderation_transitions_are_enforced(): void
    {
        [$order, $item] = $this->completedOrderItem();
        $review = Review::create(['user_id' => null, 'product_id' => $item->variant->product_id, 'order_item_id' => $item->id, 'author_name' => 'خریدار', 'rating' => 4, 'body' => 'خوب بود', 'status' => 'pending']);

        $this->patch(route('admin.reviews.status', $review), ['status' => 'hidden'])->assertRedirect()->assertSessionHas('error');
        $this->assertSame('pending', $review->fresh()->status);

        $this->patch(route('admin.reviews.status', $review), ['status' => 'approved'])->assertRedirect()->assertSessionHas('success');
        $this->assertSame('approved', $review->fresh()->status);
        $this->assertNotNull($review->fresh()->reviewed_at);

        $this->patch(route('admin.reviews.status', $review), ['status' => 'hidden'])->assertRedirect()->assertSessionHas('success');
        $this->assertSame('hidden', $review->fresh()->status);
    }

    public function test_storefront_shows_only_approved_reviews(): void
    {
        [$order, $item, $product] = $this->completedOrderItem();
        Review::create(['product_id' => $product->id, 'order_item_id' => $item->id, 'author_name' => 'خریدار', 'rating' => 5, 'body' => 'نظر منتشرشده', 'status' => 'approved', 'reviewed_at' => now()]);
        Review::create(['product_id' => $product->id, 'author_name' => 'کاربر دیگر', 'rating' => 1, 'body' => 'نظر در انتظار', 'status' => 'pending']);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('نظر منتشرشده')
            ->assertDontSee('نظر در انتظار');
    }
}
