<?php


namespace App\Http\Controllers;

use App\Events\PostCreatedEvent;
use App\Events\NewChatEvent;
use App\Http\Controllers\Controller;
use App\Models\Words\Word;
use \Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class NesCssController extends Controller {

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct() {

    }


    /**
     * Show the words management dashboard.
     * @return \Illuminate\Http\Response
     */
    public function index($gameId) {
        return view('dashboards.nes_play_1', [
            'gameId' => $gameId,
            'currentUserId' => auth()->user()->id,
        ]);
    }

    public function sendChatMessage($gameId, Request $request){
        $message = $request->input('message');
        $event = new NewChatEvent($gameId, $message, auth()->user()->id, auth()->user()->name);
        event($event);
        return json_encode($event);
    }

    public function event(){
        $event = new PostCreatedEvent(['chat' => 'pong']);
        event($event);
        dd($event);
    }
}