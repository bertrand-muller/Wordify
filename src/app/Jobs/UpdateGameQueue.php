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

    public $tries = 3;

    private $gameId;
    private $nextStep;
    private $currentRound;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($gameId, $nextStep, $currentRound)
    {
        $this->gameId = $gameId;
        $this->nextStep = $nextStep;
        $this->currentRound = $currentRound;
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

        if($round->step < $this->nextStep && $gameData->currentRound == $this->currentRound){ // if action not already performed
            switch ($this->nextStep){
                case 2:
                    /*$playersWord = json_decode($game->playersWord);
                    foreach ($round->words as $words){
                        if(!$words->done){
                            $playersWord->{$words->id} = null;
                        }
                    }
                    $game->playersWord = json_encode($playersWord);*/
                    $controller->goToSelectStep($game, $round, $gameData);
                    break;
                case 3:
                    $controller->goToGuessStep($game, $round, $gameData);
                    break;
                case 4:
                    $controller->passChooser($game->key, true);
                    break;
                case 5:
                    if($gameData->currentRound == $gameData->nbRounds){
                        $controller->end($game);
                    }else{
                        $controller->start($game->key, true);
                    }
                    break;
                case 6:
                    try {
                        $controller->createWithGame($game, $gameData);
                    }catch (\Exception $e){
                        $event = new NewChatEvent($game->id,$e->getTraceAsString(),1,'Admin','');
                        event($event);
                    }
                    break;
                default:
            }
        }
    }
}
