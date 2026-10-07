<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cartService, private readonly OrderService $orderService) {}

    public function create(Request $request): View|RedirectResponse
    {
        $cart = $this->cartService->currentCart($request)->load('items.variant.product', 'items.variant.color', 'items.variant.size');
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'برای ادامه، ابتدا محصولی به سبد خرید اضافه کنید.');
        }

        return view('storefront.checkout', ['cart' => $cart, 'totals' => $this->cartService->totals($cart)]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $order = $this->orderService->createFromCart($request, $request->validated());

        return redirect()->route('orders.track', ['token' => $order->access_token])->with('success', 'سفارش شما با موفقیت ثبت شد.');
    }

    public function mockPay(Request $request, string $order): RedirectResponse
    {
        $model = $this->authorizedOrder($request, $order);
        $this->orderService->mockPay($model);

        return redirect()->route('orders.track', ['token' => $model->access_token])->with('success', 'پرداخت آزمایشی با موفقیت ثبت شد.');
    }

    private function authorizedOrder(Request $request, string $order): Order
    {
        $model = Order::query()->where('order_number', $order)->firstOrFail();
        $owned = ($model->user_id !== null && $model->user_id === $request->user()?->id)
            || ($model->user_id === null && $model->session_id === $request->session()->getId());
        $tokenValid = is_string($request->input('token')) && hash_equals((string) $model->access_token, $request->input('token'));
        abort_unless($owned || $tokenValid, 404);

        return $model;
    }
}
