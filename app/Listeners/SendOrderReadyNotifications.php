<?php

namespace App\Listeners;

use App\Events\OrderReady;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderReadyNotifications implements ShouldQueue
{
    public bool $afterCommit = true;

    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(OrderReady $event): void
    {
        $order = $event->order;
        if ($order->customer_phone === null || $order->customer_phone === '') {
            return;
        }
        $this->notifications->send('sms', 'order.ready', $order, $order->customer_phone);
        $this->notifications->send('whatsapp', 'order.ready', $order, $order->customer_phone);
    }
}
