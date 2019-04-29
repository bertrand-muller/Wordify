<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('game-{gameId}', function ($user, $gameId) {
    return [
        'userId' => $user->id,
        'userName' => $user->name,
        'userDesc' => $user->desc,
        'userImage' => $user->image,
        'gameId' => $gameId
    ];
});

Broadcast::channel('player-{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});