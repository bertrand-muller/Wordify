<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NewChatEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $gameId;
    public $message;
    public $userId;
    public $userName;
    public $userImage;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($gameId, $message, $userId, $userName, $userImage)
    {
        $this->gameId = $gameId;
        $this->message = $message;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->userImage = $userImage;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('game-'.$this->gameId);
    }
}
