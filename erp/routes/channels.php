<?php

use Illuminate\Support\Facades\Broadcast;

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
// Broadcast::routes(['middleware' => ['admin']]);
// Broadcast::channel('notification-{userId}', function ($user, $userId) {
//     return (int) $user->id === (int) $userId;
// }, ['guards' => ['admin']]);

// Broadcast::channel('notification-{userId}', function ($user, $userId) {
//     return (int) $user->id === (int) $userId;
// });

// // For employee notifications
// Broadcast::channel('notification-{userId}', function ($employee, $userId) {
//     return (int) $employee->id === (int) $userId;
// }, ['guards' => ['employee']]);
Broadcast::channel('private-channel.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
