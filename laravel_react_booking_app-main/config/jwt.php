<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Secret Key
    |--------------------------------------------------------------------------
    |
    | This key is used to sign and verify every JWT issued by the app. Set
    | JWT_SECRET in your .env to a long, random string (e.g. the output of
    | `php artisan key:generate --show`). If it's left blank, APP_KEY is
    | used instead so the API still works out of the box.
    |
    */

    'secret' => env('JWT_SECRET') ?: env('APP_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Token Time To Live
    |--------------------------------------------------------------------------
    |
    | Number of minutes an issued token stays valid before it expires and
    | the user has to log in again.
    |
    */

    'ttl' => env('JWT_TTL', 60),

];
