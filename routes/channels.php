<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado de chat entre dos usuarios
// Formato: chat.{userId1}.{userId2} donde userId1 < userId2
Broadcast::channel('chat.{user1}.{user2}', function ($user, $user1, $user2) {
    // Autorizar si el usuario autenticado es uno de los dos participantes
    return (int) $user->id === (int) $user1 || (int) $user->id === (int) $user2;
});
