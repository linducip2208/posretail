<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    public function __construct(
        public string $productName,
        public int $currentStock,
        public int $minStock,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'product' => $this->productName,
            'current_stock' => $this->currentStock,
            'min_stock' => $this->minStock,
            'message' => "Stok {$this->productName} tinggal {$this->currentStock} (min: {$this->minStock})",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Stok Rendah: {$this->productName}")
            ->line("Stok {$this->productName} tinggal {$this->currentStock} unit.")
            ->line("Batas minimum: {$this->minStock} unit.")
            ->action('Lihat Stok', url('/admin'))
            ->line('Segera lakukan pembelian ulang.');
    }
}
