<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderCreatedNotifications implements ShouldQueue
{
    public bool $afterCommit = true;

    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        if ($order->customer_phone === null || $order->customer_phone === '') {
            return;
        }
        $this->notifications->send('sms', 'order.created', $order, $order->customer_phone);
        $this->notifications->send('whatsapp', 'order.created', $order, $order->customer_phone);
    }
}
