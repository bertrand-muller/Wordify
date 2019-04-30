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

    // Registration Routes...
    if (config('auth.users.registration')) {
        Route::get('register', 'RegisterController@showRegistrationForm')->name('register');
        Route::post('register', 'RegisterController@register');
    }

    // Password Reset Routes...
    Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/reset', 'ResetPasswordController@reset');

    // Confirmation Routes...
    if (config('auth.users.confirm_email')) {
        Route::get('confirm/{user_by_code}', 'ConfirmController@confirm')->name('confirm');
        Route::get('confirm/resend/{user_by_email}', 'ConfirmController@sendEmail')->name('confirm.send');
    }

    // Social Authentication Routes...
    Route::get('social/redirect/{provider}', 'SocialLoginController@redirect')->name('social.redirect');
    Route::get('social/login/{provider}', 'SocialLoginController@login')->name('social.login');
});


Route::get('play/{gameId}', 'NesCssController@join')->name('game.play');
Route::get('update/{gameId}', 'NesCssController@updateGame')->name('game.update');
Route::post('play/{gameId}/start', 'NesCssController@start')->name('game.start');
Route::post('play/{gameId}/wordHelper', 'NesCssController@wordHelper')->name('game.wordHelper');
Route::post('play/{gameId}/selectWord', 'NesCssController@selectWord')->name('game.wordHelper');
Route::post('play/{gameId}/wordChooser', 'NesCssController@wordChooser')->name('game.wordChooser');
Route::post('play/{gameId}/passChooser', 'NesCssController@passChooser')->name('game.wordChooser');
Route::post('play/{gameId}/player/add', 'NesCssController@addPlayer')->name('game.player.add');
Route::post('play/{gameId}/player/remove', 'NesCssController@removePlayer')->name('game.player.add');
Route::get('play/{gameId}/player/hosts', 'NesCssController@start')->name('game.player.hosts');
Route::get('play', 'NesCssController@create')->name('game.create');
Route::get('test', 'NesCssController@test')->name('test');
Route::get('/', 'NesCssController@index')->name('main');


Route::post('chat/{gameId}', 'NesCssController@sendChatMessage')->name('chat.send');

// 1 - Dashboards
Route::group(['prefix' => 'dashboards', 'as' => 'dashboards.', 'namespace' => 'Dashboards'], function () {

    // 2 - Dashboard
    Route::get('/', 'UserController@index')->name('dashboard');


    // 2 - Words
    Route::group(['prefix' => 'words', 'as' => 'words.', 'namespace' => 'Words'], function() {
        Route::get('management', 'WordsManagementController@index')->name('management');
        Route::post('management/add/word', 'WordsManagementController@addWord')->name('management.add.word');
        Route::post('management/get/word', 'WordsManagementController@getWord')->name('management.get.word');
        Route::post('management/delete/word', 'WordsManagementController@deleteWord')->name('management.delete.word');
        Route::post('management/update/word', 'WordsManagementController@updateWord')->name('management.update.word');
        Route::post('management/import/words', 'WordsManagementController@importWords')->name('management.import.words');
    });


    // 2 - Users
    Route::get('users', 'UserController@index')->name('users');
    Route::get('users/{user}', 'UserController@show')->name('users.show');
    Route::get('users/{user}/edit', 'UserController@edit')->name('users.edit');
    Route::put('users/{user}', 'UserController@update')->name('users.update');
    Route::delete('users/{user}', 'UserController@destroy')->name('users.destroy');
    Route::get('permissions', 'PermissionController@index')->name('permissions');
    Route::get('permissions/{user}/repeat', 'PermissionController@repeat')->name('permissions.repeat');
    Route::get('dashboard/log-chart', 'DashboardController@getLogChartData')->name('dashboard.log.chart');
    Route::get('dashboard/registration-chart', 'DashboardController@getRegistrationChartData')->name('dashboard.registration.chart');
});




//Route::get('/', 'HomeController@index');
Route::get('/index', 'HomeController@index');

/**
 * Membership
 */
Route::group(['as' => 'protection.'], function () {
    Route::get('membership', 'MembershipController@index')->name('membership')->middleware('protection:' . config('protection.membership.product_module_number') . ',protection.membership.failed');
    Route::get('membership/access-denied', 'MembershipController@failed')->name('membership.failed');
    Route::get('membership/clear-cache/', 'MembershipController@clearValidationCache')->name('membership.clear_validation_cache');
});
