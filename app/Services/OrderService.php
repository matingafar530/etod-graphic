<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PrintJob;
use App\Models\ProductVariant;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly DatabaseManager $database) {}

    public function createFromCart(Request $request, array $customer): Order
    {
        return $this->database->transaction(function () use ($request, $customer) {
            $cart = Cart::query()->with('items.variant.product', 'items.variant.color', 'items.variant.size', 'items.upload')->where(
                $request->user() ? 'user_id' : 'session_id',
                $request->user()?->id ?? $request->session()->getId(),
            )->lockForUpdate()->first();

            if (! $cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'سبد خرید شما خالی است.']);
            }

            $order = Order::create([
                'user_id' => $request->user()?->id,
                'session_id' => $request->session()->getId(),
                'order_number' => $this->nextOrderNumber(),
                'status' => 'pending_payment',
                'payment_status' => 'pending',
                'customer_name' => $customer['customer_name'],
                'customer_phone' => $customer['customer_phone'],
                'customer_address' => $customer['customer_address'],
                'currency' => 'IRR',
            ]);

            $subtotal = 0;
            foreach ($cart->items as $cartItem) {
                $variant = ProductVariant::query()->with('product', 'color', 'size')->lockForUpdate()->findOrFail($cartItem->product_variant_id);
                $available = $variant->stock - $variant->reserved_stock;
                if (! $variant->is_active || $cartItem->quantity > $available) {
                    throw ValidationException::withMessages(['cart' => "موجودی «{$variant->product->name}» کافی نیست."]);
                }

                $variant->increment('reserved_stock', $cartItem->quantity);
                $lineTotal = $cartItem->unit_price * $cartItem->quantity;
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_snapshot' => ['sku' => $variant->sku, 'color' => $variant->color?->name, 'size' => $variant->size?->name, 'model' => $variant->model],
                    'sku' => $variant->sku,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'printing_price' => $variant->printing_price ?? $variant->product->printing_price,
                    'total_price' => $lineTotal,
                    'customization_data' => $cartItem->customization_data,
                    'preview_image_path' => $cartItem->upload?->path,
                ]);
            }

            $order->update(['subtotal' => $subtotal, 'total_price' => $subtotal]);
            $cart->items()->delete();

            return $order->fresh('items');
        });
    }

    public function mockPay(Order $order): Order
    {
        return $this->database->transaction(function () use ($order) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->payment_status === 'paid') {
                return $order;
            }
            $order->payments()->create(['provider' => 'mock', 'status' => 'paid', 'amount' => $order->total_price, 'currency' => $order->currency, 'transaction_id' => 'MOCK-'.$order->order_number, 'verified_at' => now(), 'verification_data' => ['environment' => 'development']]);
            $order->update(['status' => 'paid', 'payment_status' => 'paid', 'paid_at' => now()]);
            foreach ($order->items as $item) {
                PrintJob::firstOrCreate(
                    ['order_item_id' => $item->id],
                    ['order_id' => $order->id, 'product_variant_id' => $item->product_variant_id, 'status' => 'queued', 'preview_path' => $item->preview_image_path],
                );
            }

            foreach ($order->items as $item) {
                ProductVariant::whereKey($item->product_variant_id)->decrement('reserved_stock', $item->quantity);
            }

            return $order->fresh('items');
        });
    }

    private function nextOrderNumber(): string
    {
        $sequence = (int) Order::query()->whereYear('created_at', now()->year)->lockForUpdate()->count() + 1;

        return sprintf('ORD-%d-%06d', now()->year, $sequence);
    }
}
