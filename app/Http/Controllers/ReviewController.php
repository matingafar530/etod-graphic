<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, string $token, OrderItem $item): RedirectResponse
    {
        $order = Order::query()->where('access_token', $token)->firstOrFail();
        abort_unless($item->order_id === $order->id, 404);

        if ($order->status !== 'completed') {
            return redirect()->route('orders.track', ['token' => $token])->with('error', 'ثبت نظر پس از تکمیل سفارش فعال می‌شود.');
        }
        if ($item->review()->exists()) {
            return redirect()->route('orders.track', ['token' => $token])->with('error', 'برای این قلم قبلاً نظر ثبت شده است.');
        }
        $productId = $item->variant?->product_id;
        if (! $productId) {
            return redirect()->route('orders.track', ['token' => $token])->with('error', 'این قلم قابل نظردهی نیست.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['nullable', 'string', 'max:2000'],
            'author_name' => ['required', 'string', 'max:80'],
        ]);

        Review::create([
            'user_id' => $order->user_id,
            'product_id' => $productId,
            'order_item_id' => $item->id,
            'author_name' => $data['author_name'],
            'rating' => $data['rating'],
            'body' => $data['body'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('orders.track', ['token' => $token])->with('success', 'نظر شما ثبت شد و پس از تأیید مدیر نمایش داده می‌شود.');
    }
}
