<?php

namespace App\Jobs;

use App\Events\NewChatEvent;
use App\Http\Controllers\NesCssController;
use App\Models\Game\Game;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateGameQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $gameId;
    private $nextStep;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($gameId, $nextStep)
    {
        $this->gameId = $gameId;
        $this->nextStep = $nextStep;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $controller = new NesCssController();
        $game = Game::find($this->gameId);
        $gameData = json_decode($game->data);
        $round = $gameData->rounds[$gameData->currentRound-1];

        if($round->step < $this->nextStep){ // if action not already performed
            switch ($this->nextStep){
                case 2:
                    $playersWord = json_decode($game->playersWord);
                    foreach ($round->words as $words){
                        if(!$words->done){
                            $playersWord->{$words->id} = "XXXXXX"; // TODO intelligence
                        }
                    }
                    $game->playersWord = json_encode($playersWord);
                    $controller->goToSelectStep($game, $round, $gameData);
                    break;
                case 3:
                    // TODO select intelligence
                    $controller->goToGuessStep($game, $round, $gameData);
                    break;
                case 4:
                   // try {
                        $controller->passChooser($game->key, true);
                    /*}catch (\Exception $e){
                        $event = new NewChatEvent($game->id,'error '.$e->getMessage(),1,'Admin','');
                        event($event);
                    }*/
                    break;
                default:
                    $event = new NewChatEvent($game->id,'TODO '.$this->nextStep,1,'Admin','');
                    event($event);
            }
        }
    }
}
