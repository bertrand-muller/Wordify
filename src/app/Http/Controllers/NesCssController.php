<?php


namespace App\Http\Controllers;

use App\Events\GameEvent;
use App\Events\NewChatEvent;
use App\Http\Controllers\Controller;
use App\Jobs\UpdateGameQueue;
use App\Models\Auth\User\User;
use App\Models\Game\Game;
use App\Models\Words\Word;
use \Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class NesCssController extends Controller {
    private static $TIMER_WORD_HELPER = 30;
    private static $TIMER_WORD_SELECT = 30;
    private static $TIMER_WORD_CHOOSE = 30;
    private static $TIMER_NEXT_ROUND = 10;
    private static $TIMER_NEXT_GAME = 10;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct() {
        //$this->middleware('auth');
    }

    private function broacastWord($game, $word){
        $gameData = json_decode($game->data);
        $round = $gameData->rounds[$gameData->currentRound-1];
        foreach ($round->words as $playerWord){ // for all helpers
            $event = new GameEvent($playerWord->id, $game->data, 'player', $word);
            event($event);
        }

        $event = new GameEvent($round->chooserId, $game->data, 'player');
        event($event);
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function getGame($gameId){
        $gameId = filter_var($gameId, FILTER_SANITIZE_SPECIAL_CHARS);
        $game = Game::where('key',$gameId)->first();
        if(!$game){
            abort(404,'Game not found');
        }
        return $game;
    }

    private function getUser($userId){
        $userId = intval(filter_var($userId, FILTER_SANITIZE_NUMBER_INT));
        $user = User::find($userId);
        if(!$user){
            abort(404,'User not found');
        }
        return $user;
    }

    private function getUserDatas($user, $game = null){
        return [
            'id' => $user->id,
            'name' => $user->name,
            'image' => $user->image,
            'here' => $game ? $this->isInGame($game, $user->id) : true
        ];
    }

    public function definition($word){
        // TODO call API
        return <<<WORD
{
  "word": "$word",
  "definitions": [
    {
      "definition": "a motor vehicle with four wheels; usually propelled by an internal combustion engine",
      "partOfSpeech": "noun"
    },
    {
      "definition": "the compartment that is suspended from an airship and that carries personnel and the cargo and the power plant",
      "partOfSpeech": "noun"
    },
    {
      "definition": "where passengers ride up and down",
      "partOfSpeech": "noun"
    },
    {
      "definition": "a wheeled vehicle adapted to the rails of railroad",
      "partOfSpeech": "noun"
    },
    {
      "definition": "a conveyance for passengers or freight on a cable railway",
      "partOfSpeech": "noun"
    }
  ]
}
WORD;
        ;
    }

    public function join($gameId) {
        $game = $this->getGame($gameId);
        $user = $this->getAuthUser();

        if(!$this->isPlaceInRoom($game) && !$this->isInGame($game, $user->id)){
            return redirect(route('index'))->withErrors([$user->getAuthIdentifierName() => 'There is no space anymore']);
        }

        return view('dashboards.nes_play_1', [
            'gameId' => $game->id,
            'gameKey' => $game->key,
            'game' => $game->data,
            'words' => $this->getCurrentPlayersWord($game),
            'currentUserId' => $user->id,
            'currentUserName' => $user->name,
            'currentWord' => $this->getCurrentWord($game),
        ]);
    }

    /*public function addPlayer(Request $request, $gameId){
        $game = $this->getGame($gameId);
        $user = $this->getUser($request->input('userId'));

        $gameData = json_decode($game->data);
        $gameData->players->{$user->id} = $this->getUserDatas($user);
        $game->data = json_encode($gameData);
        $game->nbPlayers++;
        if($game->nbPlayers > 7){
            return (new Response)->setStatusCode(400);
        }
        $game->save();
        //$event = new GameEvent($game->id, $game->data, 'game');
        //event($event);
        return json_encode($game->data);
    }

    public function removePlayer(Request $request, $gameId){
        $game = $this->getGame($gameId);
        $user = $this->getUser($request->input('userId'));

        $gameData = json_decode($game->data);
        if(!isset($gameData->players->{$user->id})){
            return (new Response)->setStatusCode(202)->setContent(json_encode("ok"));
        }else {
            unset($gameData->players->{$user->id});
            $previousHost = $gameData->hostId;
            if ($user->id == $previousHost) {
                $players = get_object_vars($gameData->players);
                $playersIds = array_keys($players);
                $newHost = $players[$playersIds[rand(0, sizeof($playersIds) - 1)]];
                $gameData->hostId = $newHost->id;
                $gameData->hostName = $newHost->name;
            }
            $game->data = json_encode($gameData);
            $game->nbPlayers--;
            $game->save();
            if ($user->id == $previousHost) {
                $event = new GameEvent($game->id, $game->data, 'game');
                event($event);
            }
            return json_encode("ok");
        }
    }*/

    public function getHost($gameId){
        sleep(1);
        $game = $this->getGame($gameId);

        $gameData = json_decode($game->data);
        $previousHost = $gameData->hostId;
        $users = $this->getUsersInRoom($game);

        foreach ($users as $user){
            if($user->id == $previousHost){
                return response(json_encode("ok"), 202);
            }
        }

        $newHost = User::find($users[rand(0, sizeof($users) - 1)]->id);
        $gameData->hostId = $newHost->id;
        $gameData->hostName = $newHost->name;
        $game->data = json_encode($gameData);
        $game->save();
        $event = new GameEvent($game->id, $game->data, 'game');
        event($event);
        return json_encode("ok");

    }

    private function getUsersInRoom($game){
        $ch = curl_init();

        // Check if initialization had gone wrong*
        if ($ch === false) {
            throw new Exception('failed to initialize');
        }

        curl_setopt($ch, CURLOPT_URL, 'http://echo:6001/apps/bf3ca786357a179b/channels/presence-game-'.$game->id.'/users?auth_key=e9f35bc4827c0917763c91553f7d151f');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $content = curl_exec($ch);

        // Check the return value of curl_exec(), too
        if ($content === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        return json_decode($content)->users;
    }

    public function isPlaceInRoom($game){
        $ch = curl_init();

        // Check if initialization had gone wrong*
        if ($ch === false) {
            throw new Exception('failed to initialize');
        }

        curl_setopt($ch, CURLOPT_URL, 'http://echo:6001/apps/bf3ca786357a179b/channels/presence-game-'.$game->id.'/users?auth_key=e9f35bc4827c0917763c91553f7d151f');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $content = curl_exec($ch);

        // Check the return value of curl_exec(), too
        if ($content === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        return count(json_decode($content)->users) < 7; // TODO -> 7
    }

    public function isInGame($game, $userId){
        $ch = curl_init();

        // Check if initialization had gone wrong*
        if ($ch === false) {
            throw new Exception('failed to initialize');
        }

        curl_setopt($ch, CURLOPT_URL, 'http://echo:6001/apps/bf3ca786357a179b/channels/presence-game-'.$game->id.'/users?auth_key=e9f35bc4827c0917763c91553f7d151f');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $content = curl_exec($ch);

        // Check the return value of curl_exec(), too
        if ($content === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        $users = json_decode($content)->users;
        foreach ($users as $user){
            if($user->id == $userId){
                return true;
            }
        }
        return false;
    }

    private function getChooser($gameData){
        // Choose current player
        $players = get_object_vars($gameData->players);
        // initialize number of games
        foreach ($players as $player){
            $player->nbGames = 0;
        }
        $min = 0;
        // set number of games
        foreach ($gameData->rounds as $round){
            if(isset($players[$round->chooserId])){
                $players[$round->chooserId]->nbGames += 1;
                $min = $players[$round->chooserId]->nbGames;
            }
        }
        // search for min number of games
        foreach ($players as $player){
            if($player->nbGames < $min){
                $min = $player->nbGames;
            }
        }
        // get only players at min games
        $playersToBeChooser = [];
        foreach ($players as $player){
            if($player->nbGames == $min){
                $playersToBeChooser[] = $player;
            }
        }
        $playersIds = array_keys($playersToBeChooser);
        return $playersToBeChooser[$playersIds[rand(0,sizeof($playersIds)-1)]];
    }

    public function wordChooser($gameId, Request $request){
        $game = $this->getGame($gameId);
        $user = auth()->user();

        $word = $request->input('word');

        $gameData = json_decode($game->data);

        $round = $gameData->rounds[$gameData->currentRound-1];

        if($user->id != $round->chooserId) {
            return (new Response)->setStatusCode(401);
        }
        if($round->step != 3){
            return (new Response)->setStatusCode(400);
        }

        $percent = null;
        similar_text($game->currentWord, $word, $percent);
        $round->win = $percent > 90 ? 1 : -1;
        $playerWords = json_decode($game->playersWord);
        foreach ($round->words as $roundWord){
            $roundWord->word = $playerWords->{$roundWord->id};
        }
        $round->word = $game->currentWord;
        $round->guessWord = $word;
        $this->goToFinalStep($game, $round, $gameData);
        return json_encode("ok");
    }

    public function passChooser($gameId, $forcePass = false){
        $game = $this->getGame($gameId);
        $user = auth()->user();

        $gameData = json_decode($game->data);

        $round = $gameData->rounds[$gameData->currentRound-1];

        if(!$forcePass) {
            if ($user->id != $round->chooserId) {
                return (new Response)->setStatusCode(401);
            }
        }
        if($round->step != 3){
            return (new Response)->setStatusCode(400);
        }

        $round->word = $game->currentWord;
        $round->guessWord = null;
        $round->win = 0;
        $playerWords = json_decode($game->playersWord);
        foreach ($round->words as $word){
            $word->word = $playerWords->{$word->id};
        }
        $this->goToFinalStep($game, $round, $gameData);
        return json_encode("ok");
    }

    public function selectWord($gameId, Request $request){
        $game = $this->getGame($gameId);
        $user = auth()->user();

        $userId = $request->input('userId');
        $choice = $request->input('choice');

        $gameData = json_decode($game->data);

        $round = $gameData->rounds[$gameData->currentRound-1];

        if($user->id == $round->chooserId || !isset($gameData->players->{$user->id})) {
            return (new Response)->setStatusCode(401);
        }
        if($round->step != 2){
            return (new Response)->setStatusCode(400);
        }
        if(!isset($round->words->{$userId})){
            return (new Response)->setStatusCode(400);
        }
        if(isset($round->words->{$userId}->select->{$user->id})){
            return (new Response)->setStatusCode(202)->setContent(json_encode("ok"));
        }

        switch ($choice){
            case "true":
                $choice = true;
                break;
            case "false":
                $choice = false;
                break;
            default:
                $choice = null;
        }

        $round->words->{$userId}->select->{$user->id} = $choice;

        // check if all players have play
        $allWords = true;
        foreach ($round->words as $words){
            if(count((array) $words->select) != count((array) $round->words)){
                $allWords = false;
            }
        }

        if ($allWords) {
            $this->goToGuessStep($game, $round, $gameData);
        }else{
            $game->data = json_encode($gameData);
            $game->save();
            $this->broacastWord($game, json_encode(['words' => $game->playersWord]));
        }
        return json_encode("ok");
    }

    private function getAuthUser(){
        $user = auth()->user();
        if ($user == null) {
            $user = new User();
            do {
                $int = rand(10000, 99999);
            } while (!User::where('email', "guest-$int@localhost.$int")->get()->isEmpty());
            $user->name = "Guest $int";
            $user->email = "guest-$int@localhost.$int";
            $user->password = $this->generateRandomString();
            $user->active = 1;
            $user->confirmed = true;
            $user->isGuest = true;
            $user->save();
            Auth::loginUsingId($user->id);
        }
        return $user;
    }

    public function wordHelper($gameId, Request $request){
        $game = $this->getGame($gameId);
        $user = auth()->user();

        $gameData = json_decode($game->data);

        $round = $gameData->rounds[$gameData->currentRound-1];

        if($user->id == $round->chooserId || !isset($gameData->players->{$user->id})) {
            return (new Response)->setStatusCode(401);
        }
        if($round->step != 1){
            return (new Response)->setStatusCode(400);
        }

        if($round->words->{$user->id}->done){
            return (new Response)->setStatusCode(202)->setContent(json_encode("ok"));
        }

        $playersWord = json_decode($game->playersWord);
        $playersWord->{$user->id} = filter_var($request->input('word'), FILTER_SANITIZE_SPECIAL_CHARS);
        $round->words->{$user->id}->done = true;

        // check if all players have play
        $allWords = true;
        foreach ($round->words as $words){
            if(!$words->done){
                $allWords = false;
            }
        }

        $game->playersWord = json_encode($playersWord);
        if ($allWords) {
            $this->goToSelectStep($game, $round, $gameData);
        } else {
            $game->data = json_encode($gameData);
            $game->save();
            $event = new GameEvent($game->id, $game->data, 'game');
            event($event);
        }
        return json_encode("ok");
    }

    public function goToSelectStep($game, $round, $gameData){
        $round->step = 2;
        $round->nextStepTimer = date('U')+self::$TIMER_WORD_SELECT;
        $round->timer = self::$TIMER_WORD_SELECT;
        $game->data = json_encode($gameData);
        $game->save();
        $this->broacastWord($game, json_encode(['words' => $game->playersWord]));
        $this->dispatch((new UpdateGameQueue($game->id, 3, $gameData->currentRound))->delay(self::$TIMER_WORD_SELECT));
    }

    public function end($game){
        $gameData = json_decode($game->data);
        $round = $gameData->rounds[$gameData->currentRound-1];
        $gameData->gameStatus = 'finished';
        $game->status = 'finished';
        $round->step = 5;
        $round->nextStepTimer = date('U')+self::$TIMER_NEXT_GAME;
        $round->timer = self::$TIMER_NEXT_GAME;
        $game->data = json_encode($gameData);
        $game->save();
        $event = new GameEvent($game->id, $game->data, 'game');
        event($event);
        $this->dispatch((new UpdateGameQueue($game->id, 6, $gameData->currentRound))->delay(self::$TIMER_NEXT_GAME));
    }

    public function goToFinalStep($game, $round, $gameData){
        $round->step = 4;
        $round->nextStepTimer = date('U')+self::$TIMER_NEXT_ROUND;
        $round->timer = self::$TIMER_NEXT_ROUND;
        $game->data = json_encode($gameData);
        $game->save();
        $event = new GameEvent($game->id, $game->data, 'game');
        event($event);
        $this->dispatch((new UpdateGameQueue($game->id, 5, $gameData->currentRound))->delay(self::$TIMER_NEXT_ROUND));
    }

    public function goToGuessStep($game, $round, $gameData){
        $round->step = 3;
        $playersWord = json_decode($game->playersWord);
        foreach ($round->words as $words){
            $weight = 0;
            foreach ($words->select as $select){
                switch (json_encode($select)){
                    case 'true':
                        $weight += 1;
                        break;
                    case 'false':
                        $weight -= 1;
                        break;
                    case 'null':
                        break;
                }
            }
            if($weight > 0){
                $words->word = $playersWord->{$words->id};
                $words->isSelected = true;
            }else{
                $words->isSelected = false;
            }
        }

        $round->nextStepTimer = date('U')+self::$TIMER_WORD_CHOOSE;
        $round->timer = self::$TIMER_WORD_CHOOSE;
        $game->data = json_encode($gameData);
        $game->save();
        $event = new GameEvent($game->id, $game->data, 'game');
        event($event);
        $this->dispatch((new UpdateGameQueue($game->id, 4, $gameData->currentRound))->delay(self::$TIMER_WORD_CHOOSE));
    }

    public function start($gameId, $autoQueue = false){
        $game = $this->getGame($gameId);

        $gameData = json_decode($game->data);

        $users = $this->getUsersInRoom($game);

        if(!$autoQueue) {
            if ($gameData->hostId != auth()->user()->id) {
                return response('Not host', 400);
            }
        }
        if(count($users) < 2){return response('Not enought players', 400);} // todo -> 3
        if(count($users) > 7){return response('Too much players', 400);}

        unset($gameData->players);
        $gameData->players = new \stdClass();

        foreach ($users as $userId) {
            $user = User::find($userId->id);
            $gameData->players->{$user->id} = $this->getUserDatas($user, $game);
        }
        $game->data = json_encode($gameData);

        $gameData = json_decode($game->data);
        $chooser = $this->getChooser($gameData);

        $words = [];
        $playerWords = [];
        foreach ($gameData->players as $user) {
            if($user->id != $chooser->id) {
                $words[$user->id] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'done' => false,
                    'word' => null,
                    'isSelected' => null,
                    'select' => new \stdClass()
                ];
                $playerWords[$user->id] = "";
            }
        }

        $gameData->currentRound++;
        $gameData->gameStatus = 'running';
        $game->status = 'running';

        $gameData->rounds[] = [
            'id' => $gameData->currentRound,
            'step' => 1,
            'nextStepTimer' => date('U')+self::$TIMER_WORD_HELPER,
            'timer' => self::$TIMER_WORD_HELPER,
            'word' => null,
            'guessWord' => null,
            'win' => null,
            'chooserId' => $chooser->id,
            'chooserName' => $chooser->name,
            'words' => $words
        ];

        $game->playersWord = json_encode($playerWords);
        $word = $this->generateRandomString(10);

        $game->data = json_encode($gameData);
        $game->currentWord = $word;
        $game->save();
        $this->broacastWord($game, json_encode(['word' => $word]));
        $this->dispatch((new UpdateGameQueue($game->id, 2, $gameData->currentRound))->delay(self::$TIMER_WORD_HELPER));
        return json_encode("ok");
    }

    public function randomJoin(){
        $games = Game::where([
            ['status', '=',  'begin'],
            ['isPrivate', '=', false],
        ])->get();
        foreach ($games as $key => $game){
            if(!$this->isPlaceInRoom($game)){
                $games->forget($key);
            }
        }
        $nbGames = $games->count();
        if($nbGames == 0){
            $games = Game::where([
                ['status', '=',  'running'],
                ['isPrivate', '=', false],
            ])->get();
            foreach ($games as $key => $game){
                if(!$this->isPlaceInRoom($game)){
                    $games->forget($key);
                }
            }
            $nbGames = $games->count();
            if($nbGames == 0){
                return $this->createAuto();
            }
        }
        $game = $games->get($games->keys()[rand(0,$nbGames-1)]);
        return redirect()->route('game.play', ['gameId' => $game->key]);
    }

    public function createAuto(){
        $game = $this->createGame();
        return redirect()->route('game.play', ['gameId' => $game->key]);
    }

    public function createWithGame($game, $gameData){
        $newGame = $this->createGame($gameData->nbRounds, $game->isPrivate);
        $gameData = json_decode($game->data);
        $gameData->nextGame = $newGame->key;
        $game->data = json_encode($gameData);
        $game->save();
        $event = new GameEvent($game->id, $game->data, 'game');
        event($event);
        return json_encode("ok");
    }

    private function createGame($nbRounds = 5, $private = false){
        $game = new Game();
        do {
            $game->key = $this->generateRandomString(6);
        }while(!Game::where('key', $game->key)->get()->isEmpty());
        $data = [
            'players' => new \stdClass(),
            'currentRound' => 0,
            'nbRounds' => $nbRounds,
            'gameStatus' => 'begin',
            'hostName' => null,
            'hostId' => null,
            'rounds' => []
        ];
        $game->isPrivate = $private;
        $game->status = 'begin';
        $game->data = json_encode($data);
        $game->save();

        return $game;
    }

    public function createWithParams(Request $request){
        $nbRounds = intval(filter_var($request->input('nbRounds'), FILTER_SANITIZE_NUMBER_INT));
        if($nbRounds < 1 || $nbRounds > 10){
            $nbRounds = 5;
        }
        $isPrivate = $request->input('isPrivate') === 'yes' ? true : false;
        $game = $this->createGame($nbRounds, $isPrivate);
        return redirect()->route('game.play', ['gameId' => $game->key]);
    }

    /*public function test(){
        try {
            $ch = curl_init();

            // Check if initialization had gone wrong*
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }

            curl_setopt($ch, CURLOPT_URL, 'http://echo:6001/apps/bf3ca786357a179b/channels?auth_key=e9f35bc4827c0917763c91553f7d151f');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $content = curl_exec($ch);

            // Check the return value of curl_exec(), too
            if ($content === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            dd($content);


            // Close curl handle
            curl_close($ch);
        } catch(Exception $e) {

            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);

        }
    }*/

    private function getCurrentPlayersWord($game){
        $gameData = json_decode($game->data);

        if($gameData->currentRound > 0) {
            $round = $gameData->rounds[$gameData->currentRound - 1];
            if (auth()->user()->id != $round->chooserId && $round->step > 1) {
                return $game->playersWord;
            }
        }
        return null;
    }

    private function getCurrentWord($game){
        $gameData = json_decode($game->data);

        if($gameData->currentRound > 0) {
            $round = $gameData->rounds[$gameData->currentRound - 1];
            if (auth()->user()->id != $round->chooserId) {
                return $game->currentWord;
            }
        }
        return null;
    }

    public function sendChatMessage($gameId, Request $request){
        $message = $request->input('message');
        $event = new NewChatEvent($gameId, $message, auth()->user()->id, auth()->user()->name, auth()->user()->image);
        event($event);
        return json_encode($event);
    }

    public function index(){
        $user = $this->getAuthUser();
        return view('dashboards.index', [
            'user' => $user
        ]);
    }

    public function admin(){
        $user = $this->getAuthUser();
        if($user->roles()->where('name', 'administrator')->exists()) {
            return view('dashboards.admin', [
                'user' => $user,
                'words' => Word::where('valid', true)->get(),
                'wordsToValidate' => Word::where('valid', false)->get(),
            ]);
        }else{
            return response("Not admin",401);
        }
    }
}