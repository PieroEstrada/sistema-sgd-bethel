<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $messageIds;
    public int $userId;
    public string $readAt;
    public int $otherUserId;

    /**
     * Create a new event instance.
     */
    public function __construct(array $messageIds, int $userId, int $otherUserId, string $readAt)
    {
        $this->messageIds = $messageIds;
        $this->userId = $userId;
        $this->otherUserId = $otherUserId;
        $this->readAt = $readAt;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        $conversationId = Message::getConversationId($this->userId, $this->otherUserId);

        return new PrivateChannel($conversationId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.read';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message_ids' => $this->messageIds,
            'user_id' => $this->userId,
            'read_at' => $this->readAt,
        ];
    }
}
