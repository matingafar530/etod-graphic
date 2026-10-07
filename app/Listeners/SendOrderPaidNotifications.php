<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderPaidNotifications implements ShouldQueue
{
    public bool $afterCommit = true;

    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(OrderPaid $event): void
    {
        $order = $event->order;
        if ($order->customer_phone === null || $order->customer_phone === '') {
            return;
        }
        $this->notifications->send('sms', 'order.paid', $order, $order->customer_phone);
        $this->notifications->send('whatsapp', 'order.paid', $order, $order->customer_phone);
    }
}
