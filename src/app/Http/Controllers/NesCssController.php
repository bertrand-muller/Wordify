<?php


namespace App\Http\Controllers;

use App\Events\GameEvent;
use App\Events\NewChatEvent;
use App\Http\Controllers\Controller;
use App\Jobs\UpdateGameQueue;
use App\Models\Auth\User\User;
use App\Models\Game\Game;
use App\Models\Words\Counter;
use App\Models\Words\Word;
use App\Models\Words\WordDatas;
use \Exception;
use function GuzzleHttp\Psr7\str;
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
    private static $WORD_REQUEST_LIMIT = 2400;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct() {
        //$this->middleware('auth');
    }

    // TODO remove
    public function addWord(){
        $words = Word::all();
        $i = 0;
        $errors = [];
        $treated = [];
        $string = '$words = [';
        foreach ($words as $word){
            if($i < 50) {
                $wordDatas = WordDatas::where('word', strtolower($word->word))->first();
                if (!$wordDatas) {
                    try {
                        $this->getWordDatas($word->word);
                    }catch (Exception $e){}
                    $i++;
                }else{
                    if(!isset(json_decode($wordDatas->datas)->results)){
                        $errors[] = $word->word;
                    }else{
                        $treated[] = $word->word;
                        $string .= '["'.$wordDatas->word.'", '.json_encode($wordDatas->datas).'],'."\n";
                    }
                }
            }
        }
        $string .= '];';
        print $string;
    }

    private function broacastWord($game, $word, $updatePlayer = false){
        $gameData = json_decode($game->data);
        $round = $gameData->rounds[$gameData->currentRound-1];
        $notWatchers = [];

        foreach ($round->words as $playerWord){ // for all helpers
            $event = new GameEvent($playerWord->id, $game->data, 'player', $word, $updatePlayer);
            event($event);
            $notWatchers[$playerWord->id] = $playerWord->id;
        }

        $notWatchers[$round->chooserId] = $round->chooserId;
        $event = new GameEvent($round->chooserId, $game->data, 'player', null, $updatePlayer);
        event($event);

        $watchers = $this->getUsersInRoom($game);
        foreach ($watchers as $watcher){
            if(!isset($notWatchers[$watcher->id])){
                $event = new GameEvent($watcher->id, $game->data, 'player', null, $updatePlayer);
                event($event);
            }
        }
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
            abort(404, 'Game not found');
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

    private function getWordDatas($word, $types = ['definition']){
        $word = strtolower($word);
        $datas = WordDatas::where('word', $word)->first();
        if(count($datas) == 0){
            $counter = Counter::where('day', date('Y-m-d'))->first();
            if(!$counter) {
                $counter = new Counter();
                $counter->day = date('Y-m-d');
                $counter->counter = 0;
            }

            if($counter->counter >= self::$WORD_REQUEST_LIMIT){
                abort(400,'Can\'t perform more request');
            }
            $counter->counter++;
            $counter->save();

            $datas = new WordDatas();
            $datas->word = $word;

            $ch = curl_init();
            // Check if initialization had gone wrong*
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }
            curl_setopt($ch, CURLOPT_URL, 'https://wordsapiv1.p.rapidapi.com/words/'.str_replace(' ','%20', $word));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-RapidAPI-Host: wordsapiv1.p.rapidapi.com',
                'X-RapidAPI-Key: 94c73cd02dmshca063c7b7964d30p10506cjsne7a775282985'
            ));

            $content = curl_exec($ch);
            // Check the return value of curl_exec(), too
            if ($content === false) {
                $datas_json = "";
            }else {
                $datas->datas = $content;
                $datas->save();
                $datas_json = json_decode($datas->datas);
            }
        }else{
            $datas_json = json_decode($datas->datas);
        }
        $extract = new \stdClass();
        if(isset($datas_json->results)) {
            $extract->word = ucfirst(strtolower($datas_json->word));
            foreach ($types as $type) {
                $extract->{$type} = [];
                foreach ($datas_json->results as $property => $datas_json_res) {
                    if (isset($datas_json_res->{$type})) {
                        $extract->{$type}[] = $datas_json_res->{$type};
                    }
                }
                $extract->{$type} = $this->flattenArray($extract->{$type});
            }
        }else{
            $extract->word = $word;
            foreach ($types as $type) {
                $extract->{$type} = [];
            }
        }
        return json_encode($extract);
    }

    private function flattenArray($arrayToFlatten) {
        $flatArray = array();
        foreach($arrayToFlatten as $element) {
            if (is_array($element)) {
                $flatArray = array_merge($flatArray, $this->flattenArray($element));
            } else {
                $flatArray[] = $element;
            }
        }
        return $flatArray;
    }

    public function definition($word){
        $this->updateUserStat(auth()->user()->id, 'words_definition');
        $datas = $this->getWordDatas($word);
        return $datas;
    }

    public function wordDatas($word){
        $this->updateUserStat(auth()->user()->id, 'words_definition');
        $datas = $this->getWordDatas($word, ['definition','synonyms']);
        return $datas;
    }

    public function join($gameId) {
        try {
            $game = $this->getGame($gameId);
        }catch (Exception $e){
            return redirect(route('index'))->withErrors([auth()->user()->getAuthIdentifierName() => 'Game not found']);
        }
        $user = $this->getAuthUser();

        if(!$this->isPlaceInRoom($game) && !$this->isInGame($game, $user->id)){
            return redirect(route('index'))->withErrors([$user->getAuthIdentifierName() => 'There is no space anymore']);
        }

        return view('dashboards.nes_play_1', [
            'gameId' => $game->id,
            'gameKey' => $game->key,
            'game' => $game->data,
            'gameNbPlayers' => $game->nbPlayers,
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

        curl_setopt($ch, CURLOPT_URL, config('app.echo_url').'/apps/bf3ca786357a179b/channels/presence-game-'.$game->id.'/users?auth_key=e9f35bc4827c0917763c91553f7d151f');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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

        curl_setopt($ch, CURLOPT_URL, config('app.echo_url').'/apps/bf3ca786357a179b/channels/presence-game-'.$game->id.'/users?auth_key=e9f35bc4827c0917763c91553f7d151f');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $content = curl_exec($ch);

        // Check the return value of curl_exec(), too
        if ($content === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        return count(json_decode($content)->users) < $game->nbPlayers;
    }

    public function isInGame($game, $userId){
        $ch = curl_init();

        // Check if initialization had gone wrong*
        if ($ch === false) {
            throw new Exception('failed to initialize');
        }

        curl_setopt($ch, CURLOPT_URL, config('app.echo_url').'/bf3ca786357a179b/channels/presence-game-'.$game->id.'/users?auth_key=e9f35bc4827c0917763c91553f7d151f');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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

    private function getNewWord($gameData){
        $wordsAlreadyTaked = [];
        foreach ($gameData->rounds as $round){
            $wordsAlreadyTaked[] = ['word','!=',$round->word];
        }

        $words = Word::where($wordsAlreadyTaked)->get();

        return $words->get(rand(0,count($words)-1))->word;
    }

    public function wordChooser($gameId, Request $request, $forcePass = null){
        $game = $this->getGame($gameId);
        $user = auth()->user();

        if($forcePass){
            $word = $forcePass;
        }else {
            $word = filter_var($request->input('word'), FILTER_SANITIZE_STRING);
        }
        $word = ucfirst(strtolower($word));

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

        $percent = null;
        similar_text(strtolower($game->currentWord), strtolower($word), $percent);
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
        $playersWord->{$user->id} = ucfirst(strtolower(filter_var($request->input('word'), FILTER_SANITIZE_SPECIAL_CHARS)));
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
        $words = [];
        $playersWord = json_decode($game->playersWord);
        foreach ($round->words as $word){
            $words[$word->id] = ['id' => $word->id, 'word' => $playersWord->{$word->id}, 'select' => null];
        }
        foreach ($words as $key => $currentWord){
            $select = true;
            $percent = null;
            similar_text(strtolower($game->currentWord), strtolower($currentWord['word']), $percent);
            if($percent > 90){
                $select = false;
            }else{
                foreach ($round->words as $word){
                    if($currentWord['id'] != $word->id && $select){
                        $percent = null;
                        similar_text(strtolower($playersWord->{$word->id}), strtolower($currentWord['word']), $percent);
                        $select = $percent < 90;
                    }
                }
            }
            $words[$key]['select'] = $select;
        }
        $onlyBots = true;
        foreach ($gameData->players as $player) {
            if($player->id != $round->chooserId){
                $onlyBots = $onlyBots && $player->id < 0;
            }
            foreach ($round->words as $word) {
                if($player->id < 0 && $player->id != $round->chooserId) {
                    $round->words->{$word->id}->select->{$player->id} = $words[$word->id]['select'];
                }
                if($word->done == null && $player->id != $round->chooserId){
                    $round->words->{$word->id}->select->{$player->id} = false;
                }
            }
        }
        $game->data = json_encode($gameData);
        $game->save();
        $this->broacastWord($game, json_encode(['words' => $game->playersWord]));
        $timer = $onlyBots ? 3 : self::$TIMER_WORD_SELECT;
        $this->dispatch((new UpdateGameQueue($game->id, 3, $gameData->currentRound))->delay($timer));
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

        foreach ($gameData->players as $player){
            $players[$player->id] = $player;
        }
        $score = 0;
        foreach ($gameData->rounds as $round){
            $score += $round->win;
            foreach ($players as $player){
                if(!isset($round->words->{$player->id}) && $round->chooserId != $player->id){
                    unset($players[$player->id]);
                }
            }
        }
        foreach ($players as $player){
            if($score > 0){
                $this->updateUserStat($player->id, 'games_win');
            }
            $this->updateUserStat($player->id, 'games_played');
        }
        $this->updateUserStat($gameData->hostId, 'games_host');
        $event = new GameEvent($game->id, $game->data, 'game');
        event($event);
        $this->dispatch((new UpdateGameQueue($game->id, 6, $gameData->currentRound))->delay(self::$TIMER_NEXT_GAME));
    }

    public function getProfile($userId){
        if(intval($userId) > 0) {
            $user = User::find($userId);
            if($user->isGuest){
                return json_encode(['badges' => '<label class="split"></label><div class="badges">Guests don\'t have a profile...</div>', 'name' => $user->name, 'image' => $user->image, 'email' => $this->isAdmin() ? $user->email : null]);
            }
            $stats = json_decode($user->stats);

            if ($stats->rounds_played < 10) {
                $rank = 'Newbie';
                $rankColor = 'is-dark';
            } else if ($stats->rounds_played < 50) {
                $rank = 'Novice';
                $rankColor = 'is-primary';
            } else if ($stats->rounds_played < 100) {
                $rank = 'Beginner';
                $rankColor = 'is-success';
            } else if ($stats->rounds_played < 250) {
                $rank = 'Proficient';
                $rankColor = 'is-success';
            } else if ($stats->rounds_played < 500) {
                $rank = 'Intermediate';
                $rankColor = 'is-warning';
            } else if ($stats->rounds_played < 1000) {
                $rank = 'Senior';
                $rankColor = 'is-warning';
            } else {
                $rank = 'Expert';
                $rankColor = 'is-error';
            }

            $roundsWinGuesser = $roundsWinHelper = $gamesWin = '-';
            if ($stats->rounds_played != 0) {
                $roundsWinGuesser = round($stats->rounds_win_guesser / $stats->rounds_played * 100, 1) . '%';
                $roundsWinHelper = round($stats->rounds_win_helper / $stats->rounds_played * 100, 1) . '%';
            }
            if ($stats->games_played != 0) {
                $gamesWin = round($stats->games_win / $stats->games_played * 100, 1) . '%';
            }
            $badges = <<<BADGES
<label class="split"></label>
<div class="badges">
    <label>Rank</label>
    <span class="nes-badge">
      <span class="is-dark $rankColor">$rank</span>
    </span>
    <label>Games</label>
    <span class="nes-badge is-splited">
      <span class="is-dark">Win</span>
      <span class="is-success">$gamesWin</span>
    </span>
    <span class="nes-badge is-splited">
      <span class="is-dark">Host</span>
      <span class="is-success">$stats->games_host</span>
    </span>
    <label>Rounds played</label>
    <span class="nes-badge is-splited">
      <span class="is-dark">Rounds</span>
      <span class="is-warning">$stats->rounds_played</span>
    </span>
    <span class="nes-badge is-splited">
      <span class="is-dark">Guesser</span>
      <span class="is-warning">$stats->rounds_guesser</span>
    </span>
    <label>Rounds win</label>
    <span class="nes-badge is-splited">
      <span class="is-dark">Guesser</span>
      <span class="is-success">$roundsWinGuesser</span>
    </span>
    <span class="nes-badge is-splited">
      <span class="is-dark">Helper</span>
      <span class="is-success">$roundsWinHelper</span>
    </span>
    <label>Words</label>
    <span class="nes-badge is-splited">
      <span class="is-dark">Asked</span>
      <span class="is-primary">$stats->words_definition</span>
    </span>
    <span class="nes-badge is-splited">
      <span class="is-dark">Submit</span>
      <span class="is-primary">$stats->words_submitted</span>
    </span>
</>
BADGES;
            return json_encode(['badges' => $badges, 'name' => $user->name, 'image' => $user->image, 'email' => $this->isAdmin() ? $user->email : null]);
        }else{
            return json_encode(['badges' => '<label class="split"></label><div class="badges">Bots don\'t have a profile...</div>', 'name' => 'Bot '.(-$userId), 'image' => 'bot.png', 'email' => null]);
        }
    }

    public function goToFinalStep($game, $round, $gameData){
        $round->step = 4;
        // Update stats
        foreach ($gameData->players as $player){
            $this->updateUserStat($player->id,'rounds_played');
        }
        $this->updateUserStat($round->chooserId, 'rounds_guesser');
        switch($round->win) {
            case 1:
                $this->updateUserStat($round->chooserId, 'rounds_win_guesser');
                foreach ($round->words as $word){
                    $this->updateUserStat($word->id,'rounds_win_helper');
                }
                break;
            case 0:
                $this->updateUserStat($round->chooserId, 'rounds_passed_guesser');
                break;
            case -1:
                break;
        }

        $round->nextStepTimer = date('U')+self::$TIMER_NEXT_ROUND;
        $round->timer = self::$TIMER_NEXT_ROUND;
        $game->data = json_encode($gameData);
        $game->save();
        $event = new GameEvent($game->id, $game->data, 'game');
        event($event);
        $this->dispatch((new UpdateGameQueue($game->id, 5, $gameData->currentRound))->delay(self::$TIMER_NEXT_ROUND));
    }

    public function goToGuessStep($game, $round, $gameData)
    {
        $round->step = 3;
        $playersWord = json_decode($game->playersWord);
        foreach ($round->words as $words) {
            $weight = 0;
            foreach ($words->select as $select) {
                switch (json_encode($select)) {
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
            if ($weight > 0) {
                $words->word = $playersWord->{$words->id};
                $words->isSelected = true;
            } else {
                $words->isSelected = false;
            }
        }
        if ($round->chooserId < 0) {
            $timer = 3;
        }else{
            $timer = self::$TIMER_WORD_CHOOSE;
        }
        $round->nextStepTimer = date('U') + $timer;
        $round->timer = $timer;
        $game->data = json_encode($gameData);
        $game->save();
        $event = new GameEvent($game->id, $game->data, 'game');
        event($event);
        if ($round->chooserId < 0) {
            $synonyms = [];
            foreach ($round->words as $word){
                $synonyms = array_merge($synonyms, json_decode($this->getWordDatas($word->word, ['typeOf']))->typeOf);
                $synonyms = array_merge($synonyms, json_decode($this->getWordDatas($word->word, ['hasCategories']))->hasCategories);
                $synonyms = array_merge($synonyms, json_decode($this->getWordDatas($word->word, ['synonyms']))->synonyms);
            }
            $countValues = array_count_values($synonyms);
            $wordGuessed = array_search(max($countValues), $countValues);
            $this->wordChooser($game->key,new Request(),$wordGuessed);
        }
        $this->dispatch((new UpdateGameQueue($game->id, 4, $gameData->currentRound))->delay($timer));
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
        unset($gameData->players);
        $gameData->players = new \stdClass();

        foreach ($users as $userId) {
            $user = User::find($userId->id);
            $gameData->players->{$user->id} = $this->getUserDatas($user, $game);
        }
        $nbBots = $game->nbPlayers - count($users);
        for($i=1; $i<=$nbBots; $i++){
            $gameData->players->{-$i} = [
                'id' => -$i,
                'name' => 'Bot '.$i,
                'image' => 'bot.png',
                'here' => true
            ];
        }

        $game->data = json_encode($gameData);

        $gameData = json_decode($game->data);
        $chooser = $this->getChooser($gameData);

        $word = $this->getNewWord($gameData);
        $synonymsFromDB = json_decode($this->getWordDatas($word,['hasTypes']))->hasTypes;
        $synonymsFromDB = array_merge($synonymsFromDB, json_decode($this->getWordDatas($word,['inCategory']))->inCategory);
        $synonymsFromDB = array_merge($synonymsFromDB, json_decode($this->getWordDatas($word,['synonyms']))->synonyms);

        $synonyms = [];
        foreach ($synonymsFromDB as $synonym){
            if (strpos($synonym, strtolower($word)) === false) {
                $synonyms[] = $synonym;
            }
        }

        $words = [];
        $playerWords = [];
        $onlyBots = true;
        foreach ($gameData->players as $user) {
            if($user->id != $chooser->id) {
                $words[$user->id] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'done' => $user->id < 0,
                    'word' => null,
                    'isSelected' => null,
                    'select' => new \stdClass()
                ];
                $onlyBots = $onlyBots && $user->id < 0;
                $playerWords[$user->id] = $user->id < 0 ? ucfirst(strtolower($synonyms[rand(0, count($synonyms)-1)])) : '';
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

        $game->data = json_encode($gameData);
        $game->currentWord = $word;
        $game->save();
        $timer = $onlyBots ? 3 : self::$TIMER_WORD_HELPER;
        $this->broacastWord($game, json_encode(['word' => $word]), true);
        $this->dispatch((new UpdateGameQueue($game->id, 2, $gameData->currentRound))->delay($timer));
        return json_encode("ok");
    }

    public function randomJoin(){
        $games = Game::where([
            ['status', '=',  'begin'],
            ['isPrivate', '=', false],
        ])->get();
        foreach ($games as $key => $game){
		if(!$this->isPlaceInRoom($game) || count($this->getUsersInRoom($game)) == 0){
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
                if(!$this->isPlaceInRoom($game) || count($this->getUsersInRoom($game)) == 0){
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
        $newGame = $this->createGame($gameData->nbRounds, $game->isPrivate, $game->nbPlayers);
        $gameData = json_decode($game->data);
        $gameData->nextGame = $newGame->key;
        $game->data = json_encode($gameData);
        $game->save();
        $event = new GameEvent($game->id, $game->data, 'game');
        event($event);
        return json_encode("ok");
    }

    private function createGame($nbRounds = 5, $private = false, $nbPlayers = 5){
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
        $game->nbPlayers = $nbPlayers;
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
        $nbPlayers = intval(filter_var($request->input('nbPlayers'), FILTER_SANITIZE_NUMBER_INT));
        if($nbPlayers < 3 || $nbPlayers > 7){
            $nbPlayers = 5;
        }
        $isPrivate = $request->input('isPrivate') === 'yes' ? true : false;
        $game = $this->createGame($nbRounds, $isPrivate, $nbPlayers);
        return redirect()->route('game.play', ['gameId' => $game->key]);
    }

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

    public function submitWord(Request $request){
        $wordInput = ucfirst(strtolower(filter_var($request->input('word'), FILTER_SANITIZE_SPECIAL_CHARS)));
        if(Word::where("word",$wordInput)->get()->isEmpty()) {
            $word = new Word();
            $word->word = ucfirst(strtolower($wordInput));
            $word->valid = $this->isAdmin();
            $word->userId = auth()->user()->id;
            $word->save();
            return json_encode($word);
        }else{
            return response(json_encode($wordInput), 400);
        }
    }

    public function deleteWord(Request $request){
        $this->checkIsAdmin();
        $wordId = filter_var($request->input('wordId'), FILTER_SANITIZE_NUMBER_INT);
        $word = Word::find($wordId);
        if($word) {
            $word->delete();
            return json_encode('ok');
        }else{
            return response(json_encode($wordId.' does not exist'), 400);
        }
    }

    public function validateWord(Request $request){
        $this->checkIsAdmin();
        $wordId = filter_var($request->input('wordId'), FILTER_SANITIZE_NUMBER_INT);
        $word = Word::find($wordId);
        if($word) {
            $this->updateUserStat($word->userId, 'words_submitted');
            $word->valid = true;
            $word->save();
            return json_encode($word);
        }else{
            return response(json_encode($wordId.' does not exist'), 400);
        }
    }

    private function updateUserStat($userId, $statName, $add = 1){
        $user = User::find($userId);
        if($user){
            $stats = json_decode($user->stats);
            $stats->{$statName} += $add;
            $user->stats = json_encode($stats);
            $user->save();
        }
    }

    public function index(){
        $user = $this->getAuthUser();
        return view('dashboards.index', [
            'user' => $user,
            'profile' => json_decode($this->getProfile($user->id))->badges
        ]);
    }

    private function checkIsAdmin(){
        if(!$this->isAdmin()){
            abort(401,'User is not admin');
        }
    }

    private function isAdmin(){
        return auth()->user()->id == 1;
//        return auth()->user()->roles()->where('name', 'administrator')->exists();
    }

    public function admin(){
        $user = $this->getAuthUser();
        $this->checkIsAdmin();
        return view('dashboards.admin', [
            'user' => $user,
            'words' => Word::where('valid', true)->orderBy('word', 'asc')->get(),
            'wordsToValidate' => Word::where('valid', false)->get(),
        ]);
    }
}
