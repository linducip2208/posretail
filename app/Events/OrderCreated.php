<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load(['customer', 'outlet', 'user', 'orderItems.product']);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => $this->order->total_amount,
            'order_status' => $this->order->order_status,
            'payment_status' => $this->order->payment_status,
            'outlet_name' => $this->order->outlet?->name,
            'customer_name' => $this->order->customer?->name,
            'user_name' => $this->order->user?->name,
            'items_count' => $this->order->orderItems->count(),
            'created_at' => $this->order->created_at->toIso8601String(),
        ];
    }
}
