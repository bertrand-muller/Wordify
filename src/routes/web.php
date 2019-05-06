<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/**
 * Auth routes
 */
Route::group(['namespace' => 'Auth'], function () {
    // Authentication Routes...
    Route::get('login', 'LoginController@showLoginForm')->name('login');
    Route::post('login', 'LoginController@loginFromGuest');
    Route::get('logout', 'LoginController@logout')->name('logout');
    Route::post('updateProfile', 'LoginController@updateProfile')->name('updateProfile');

    // Registration Routes...
    if (config('auth.users.registration')) {
        Route::get('register', 'RegisterController@showRegistrationForm')->name('register');
        Route::post('register', 'RegisterController@register');
    }
});

// Manage game
Route::get('play/{gameId}', 'NesCssController@join')->name('game.play');
Route::post('play/{gameId}/start', 'NesCssController@start')->name('game.start');
Route::get('play/{gameId}/start', 'NesCssController@start');
Route::post('play/{gameId}/wordHelper', 'NesCssController@wordHelper')->name('game.wordHelper');
Route::post('play/{gameId}/selectWord', 'NesCssController@selectWord')->name('game.wordHelper');
Route::post('play/{gameId}/wordChooser', 'NesCssController@wordChooser')->name('game.wordChooser');
Route::post('play/{gameId}/passChooser', 'NesCssController@passChooser')->name('game.wordChooser');
Route::get('play/{gameId}/host', 'NesCssController@getHost')->name('game.host');
Route::get('play', 'NesCssController@createAuto')->name('game.create');
Route::post('play', 'NesCssController@createWithParams');
Route::get('join', 'NesCssController@randomJoin')->name('game.randomJoin');

// Manage words
Route::post('/word/submit', 'NesCssController@submitWord')->name('word.submit');
Route::post('/word/delete', 'NesCssController@deleteWord')->name('word.delete');
Route::post('/word/validate', 'NesCssController@validateWord')->name('word.validate');
Route::get('/word/definition/{word}', 'NesCssController@definition')->name('definition');
Route::get('/word/datas/{word}', 'NesCssController@wordDatas')->name('datas');

// Post chat in game
Route::post('chat/{gameId}', 'NesCssController@sendChatMessage')->name('chat.send');

// Get profile of user
Route::get('/user/{userId}', 'NesCssController@getProfile')->name('user.profile');

// Admin page
Route::get('/admin', 'NesCssController@admin')->name('admin');

// Index page
Route::get('/', 'NesCssController@index')->name('index');

Route::get('/addWord', 'NesCssController@addWord')->name('addWord'); // TODO remove