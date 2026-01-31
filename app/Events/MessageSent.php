<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        $conversationId = Message::getConversationId(
            $this->message->from_user_id,
            $this->message->to_user_id
        );

        return new PrivateChannel($conversationId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $this->message->load(['fromUser:id,name', 'toUser:id,name']);

        return [
            'id' => $this->message->id,
            'from_user_id' => $this->message->from_user_id,
            'to_user_id' => $this->message->to_user_id,
            'message' => $this->message->message,
            'read_at' => $this->message->read_at,
            'created_at' => $this->message->created_at,
            'from_user' => [
                'id' => $this->message->fromUser->id,
                'name' => $this->message->fromUser->name,
            ],
        ];
    }
}
