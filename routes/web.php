<?php

use App\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// Spotify OAuth routes
Route::get('/auth/spotify/redirect', [OAuthController::class, 'redirect'])->name('spotify.redirect');
Route::get('/auth/spotify/callback', [OAuthController::class, 'callback'])->name('spotify.callback');
Route::get('/auth/spotify/disconnect', [OAuthController::class, 'disconnect'])->name('spotify.disconnect');
