<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public string $orderNumber,
        public string $status,
        public float $totalAmount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $labels = [
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        return [
            'type' => 'order_status',
            'order_number' => $this->orderNumber,
            'status' => $this->status,
            'total' => $this->totalAmount,
            'message' => "Order #{$this->orderNumber} " . ($labels[$this->status] ?? $this->status) . " — Rp " . number_format($this->totalAmount, 0, ',', '.'),
        ];
    }
}
