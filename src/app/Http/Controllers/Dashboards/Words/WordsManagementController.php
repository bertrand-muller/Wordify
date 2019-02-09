<?php

namespace App\Http\Controllers\Dashboards\Words;

use App\Http\Controllers\Controller;

class WordsManagementController extends Controller {

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }


    /**
     * Show the words management dashboard.
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('dashboards.words.words_management');
    }

}