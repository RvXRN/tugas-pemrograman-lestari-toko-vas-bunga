<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $userId;
    public string $status; // 'success' or 'failed'
    public ?string $message;
    public ?array $order;

    public function __construct(string $userId, string $status, ?string $message = null, ?array $order = null)
    {
        $this->userId = $userId;
        $this->status = $status;
        $this->message = $message;
        $this->order = $order;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }
    
    public function broadcastAs(): string
    {
        return 'checkout.status';
    }
}
