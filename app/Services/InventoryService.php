<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function __construct(private readonly DatabaseManager $database) {}

    public function adjust(ProductVariant $variant, int $quantity, string $reason): ProductVariant
    {
        if ($quantity === 0) {
            throw ValidationException::withMessages(['quantity' => 'مقدار تغییر موجودی نمی‌تواند صفر باشد.']);
        }

        return $this->database->transaction(function () use ($variant, $quantity, $reason) {
            $locked = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);
            $newStock = $locked->stock + $quantity;

            if ($newStock < $locked->reserved_stock) {
                throw ValidationException::withMessages(['quantity' => 'موجودی جدید نمی‌تواند کمتر از موجودی رزروشده باشد.']);
            }

            $locked->update(['stock' => $newStock]);
            InventoryMovement::create([
                'product_variant_id' => $locked->id,
                'type' => $quantity > 0 ? 'adjustment_in' : 'adjustment_out',
                'quantity' => $quantity,
                'stock_after' => $newStock,
                'reason' => $reason,
            ]);

            return $locked->fresh();
        });
    }
}
