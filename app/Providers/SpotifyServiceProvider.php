<?php

namespace App\Providers;

use App\Services\Decorators\HttpClientExceptionDecorator;
use App\Services\Decorators\RequestLoggerDecorator;
use App\Services\ExternalClient;
use App\Services\SpotifyClient;
use Illuminate\Support\ServiceProvider;

class SpotifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register ExternalClient configured for Spotify with decorators
        $this->app->singleton(ExternalClient::class, function ($app) {
            $accessToken = config('services.spotify.access_token', '');

            $client = new ExternalClient(
                baseUrl: 'https://api.spotify.com/v1',
                headers: [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                timeout: 30
            );

            // Decorate with exception handling
            $client = new HttpClientExceptionDecorator($client);

            // Decorate with request logging
            $client = new RequestLoggerDecorator($client);

            return $client;
        });

        // Register SpotifyClient with configured ExternalClient
        $this->app->singleton(SpotifyClient::class, function ($app) {
            return new SpotifyClient(
                $app->make(ExternalClient::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
