<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $owned = $order->user_id !== null && $order->user_id === $request->user()?->id;
        $sessionOwned = $order->user_id === null && $order->session_id === $request->session()->getId();
        abort_unless($owned || $sessionOwned, 404);

        return view('storefront.order', ['order' => $order->load(['items.review', 'items.variant.product', 'payments']), 'trackToken' => $order->access_token]);
    }

    public function track(string $token): View
    {
        $order = Order::query()->where('access_token', $token)->firstOrFail();

        return view('storefront.order', ['order' => $order->load(['items.review', 'items.variant.product', 'payments']), 'trackToken' => $order->access_token]);
    }

    public function trackDesign(string $token, OrderItem $item): BinaryFileResponse
    {
        $order = Order::query()->where('access_token', $token)->firstOrFail();
        abort_unless($item->order_id === $order->id, 404);
        abort_unless($item->preview_image_path && Storage::disk('local')->exists($item->preview_image_path), 404);

        return response()->file(Storage::disk('local')->path($item->preview_image_path), ['X-Content-Type-Options' => 'nosniff']);
    }
}
