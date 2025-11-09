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
        'scopes' => env('SPOTIFY_SCOPES', 'user-library-read playlist-read-private playlist-modify-public playlist-modify-private'),
        'auth_url' => env('SPOTIFY_AUTH_URL', 'https://accounts.spotify.com/authorize'),
        'token_url' => env('SPOTIFY_TOKEN_URL', 'https://accounts.spotify.com/api/token'),
    ],

];
