<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class GameEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $id;
    private $type;
    public $content;
    public $word;
    public $updatePlayers;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($id, $content, $type, $word = null, $updatePlayers = false)
    {
        $this->id = $id;
        $this->content = $content;
        $this->type = $type;
        $this->word = $word;
        $this->updatePlayers = $updatePlayers;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        switch ($this->type){
            case 'game':
                return new PresenceChannel('game-'.$this->id);
            case 'player':
                return new PrivateChannel('player-'.$this->id);
        }
    }
}
