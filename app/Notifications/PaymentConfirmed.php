<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmed extends Notification
{
    use Queueable;

    public function __construct(
        public string $orderNumber,
        public float $amount,
        public string $paymentMethod,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_confirmed',
            'order_number' => $this->orderNumber,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
            'message' => "Pembayaran Rp " . number_format($this->amount, 0, ',', '.') . " via {$this->paymentMethod} dikonfirmasi untuk Order #{$this->orderNumber}",
        ];
    }
}
