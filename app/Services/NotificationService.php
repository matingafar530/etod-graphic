<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    /**
     * Development drivers write the message to the application log and
     * the notification_logs table. Real SMS.ir / WhatsApp Cloud adapters
     * will replace the transport later; they read credentials from
     * config/etod.php only.
     */
    public function send(string $channel, string $event, ?Order $order, string $recipient, array $context = []): void
    {
        $message = $this->buildMessage($event, $order, $context);

        try {
            match ($channel) {
                'sms' => $this->transportSms($recipient, $message),
                'whatsapp' => $this->transportWhatsapp($recipient, $message),
                default => throw new \InvalidArgumentException("کانال اعلان «{$channel}» پشتیبانی نمی‌شود."),
            };
            $this->log($channel, $event, $order, $recipient, $message, 'sent');
        } catch (Throwable $exception) {
            $this->log($channel, $event, $order, $recipient, $message, 'failed', $exception->getMessage());
        }
    }

    private function transportSms(string $recipient, string $message): void
    {
        $driver = config('etod.sms_driver', 'log');
        if ($driver !== 'log') {
            throw new \RuntimeException("درایور SMS «{$driver}» هنوز پیاده‌سازی نشده است.");
        }
        Log::info('notification.sms', ['to' => $recipient, 'message' => $message]);
    }

    private function transportWhatsapp(string $recipient, string $message): void
    {
        $driver = config('etod.whatsapp_driver', 'log');
        if ($driver !== 'log') {
            throw new \RuntimeException("درایور واتساپ «{$driver}» هنوز پیاده‌سازی نشده است.");
        }
        Log::info('notification.whatsapp', ['to' => $recipient, 'message' => $message]);
    }

    private function buildMessage(string $event, ?Order $order, array $context): string
    {
        $number = $order?->order_number ?? '—';

        return match ($event) {
            'order.created' => "سفارش {$number} با موفقیت ثبت شد. برای پیگیری از لینک اختصاصی خود استفاده کنید. — Etod Graphic",
            'order.paid' => "پرداخت سفارش {$number} تأیید شد و به زودی وارد مرحله ساخت می‌شود. — Etod Graphic",
            'order.ready' => "سفارش {$number} آماده ارسال است. — Etod Graphic",
            default => "به‌روزرسانی سفارش {$number}: {$event}",
        };
    }

    private function log(string $channel, string $event, ?Order $order, string $recipient, string $message, string $status, ?string $error = null): void
    {
        DB::table('notification_logs')->insert([
            'channel' => $channel,
            'event' => $event,
            'order_id' => $order?->id,
            'recipient' => $recipient,
            'message' => $message,
            'status' => $status,
            'error' => $error,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
