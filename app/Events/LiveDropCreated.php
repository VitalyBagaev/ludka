<?php

namespace App\Events;

use App\Models\LiveDrop;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveDropCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public LiveDrop $liveDrop)
    {
        $this->liveDrop->loadMissing(['user', 'skin', 'gameCase']);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('live-drops'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'LiveDropCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'drop' => $this->liveDrop->toFrontend(),
        ];
    }
}
