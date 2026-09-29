<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Upload;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(private readonly DatabaseManager $database) {}

    public function currentCart(Request $request): Cart
    {
        $userId = $request->user()?->id;
        $sessionId = $userId ? null : $request->session()->getId();

        return Cart::firstOrCreate(
            $userId ? ['user_id' => $userId] : ['session_id' => $sessionId],
            ['user_id' => $userId, 'session_id' => $sessionId],
        );
    }

    public function add(Request $request, ProductVariant $variant, int $quantity, array $customization = []): CartItem
    {
        return $this->database->transaction(function () use ($request, $variant, $quantity, $customization) {
            $lockedVariant = ProductVariant::query()->with('product')->lockForUpdate()->findOrFail($variant->id);
            $available = $lockedVariant->stock - $lockedVariant->reserved_stock;
            $cart = $this->currentCart($request);
            $uploadId = $customization['upload_id'] ?? null;

            if ($uploadId) {
                $upload = Upload::query()->findOrFail($uploadId);
                $ownedByUser = $upload->user_id !== null && $upload->user_id === $request->user()?->id;
                $ownedBySession = $upload->user_id === null && $upload->session_id === $request->session()->getId();
                abort_unless($ownedByUser || $ownedBySession, 404);
            }

            $item = $cart->items()->where('product_variant_id', $lockedVariant->id)
                ->where('upload_id', $uploadId)->first();
            $newQuantity = ($item?->quantity ?? 0) + $quantity;

            if (! $lockedVariant->is_active || $newQuantity > $available) {
                throw ValidationException::withMessages(['quantity' => 'موجودی این گزینه کافی نیست.']);
            }

            $unitPrice = (int) (($lockedVariant->price ?? $lockedVariant->product->base_price) + ($lockedVariant->printing_price ?? $lockedVariant->product->printing_price));

            if ($item) {
                $item->update(['quantity' => $newQuantity, 'customization_data' => $customization ?: $item->customization_data, 'unit_price' => $unitPrice]);

                return $item->fresh();
            }

            return $cart->items()->create([
                'product_variant_id' => $lockedVariant->id,
                'upload_id' => $uploadId,
                'quantity' => $quantity,
                'customization_data' => $customization ?: null,
                'unit_price' => $unitPrice,
            ]);
        });
    }

    public function totals(Cart $cart): array
    {
        $subtotal = $cart->items->sum(fn (CartItem $item) => $item->unit_price * $item->quantity);

        return ['subtotal' => $subtotal, 'shipping' => 0, 'discount' => 0, 'total' => $subtotal];
    }
}
