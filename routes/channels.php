<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('queue-channel', function () {
    return true;
});

Broadcast::channel('announcement-channel', function () {
    return true;
});