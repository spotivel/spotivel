<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Spotify, Mailgun, Postmark, AWS and more. This file provides the
    | de facto location for this type of information, allowing packages
    | to have a conventional file to locate the various service credentials.
    |
    */

    'spotify' => [
        'client_id' => env('SPOTIFY_CLIENT_ID'),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
        'access_token' => env('SPOTIFY_ACCESS_TOKEN'),
        'redirect' => env('SPOTIFY_REDIRECT_URI'),
    ],

];
