<?php


namespace App\Http\Controllers\Dashboards;

use App\Events\PostCreatedEvent;
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
    public function index() {
        return view('dashboards.nes_play_1', [
        ]);
    }

    public function event(){
        $event = new PostCreatedEvent(['chat' => 'pong']);
        event($event);
        dd($event);
    }
}