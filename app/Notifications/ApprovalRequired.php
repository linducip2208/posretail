<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequired extends Notification
{
    use Queueable;

    public function __construct(
        public string $referenceType,
        public string $referenceId,
        public float $amount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'approval_required',
            'reference_type' => $this->referenceType,
            'reference_id' => $this->referenceId,
            'amount' => $this->amount,
            'message' => "Approval dibutuhkan untuk {$this->referenceType} #{$this->referenceId} — Rp " . number_format($this->amount, 0, ',', '.'),
        ];
    }
}
