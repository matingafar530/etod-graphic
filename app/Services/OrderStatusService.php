<?php

namespace App\Services;

use App\Events\OrderReady;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    private const TRANSITIONS = [
        'draft' => ['pending_payment', 'cancelled'],
        'pending_payment' => ['paid', 'payment_failed', 'cancelled'],
        'paid' => ['processing', 'cancelled'],
        'processing' => ['printing', 'cancelled'],
        'printing' => ['ready'],
        'ready' => ['shipped'],
        'shipped' => ['completed'],
        'completed' => [],
        'cancelled' => [],
        'payment_failed' => ['pending_payment', 'cancelled'],
    ];

    public function change(Order $order, string $nextStatus): Order
    {
        if (! in_array($nextStatus, self::TRANSITIONS[$order->status] ?? [], true)) {
            throw ValidationException::withMessages(['status' => "تغییر وضعیت از «{$order->status}» به «{$nextStatus}» مجاز نیست."]);
        }

        $order->update(['status' => $nextStatus]);

        if ($nextStatus === 'ready') {
            OrderReady::dispatch($order->fresh());
        }

        return $order->fresh();
    }

    public function allowedNextStatuses(string $status): array
    {
        return self::TRANSITIONS[$status] ?? [];
    }
}
