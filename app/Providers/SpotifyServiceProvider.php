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
        // Register ExternalClient with decorators
        $this->app->singleton(ExternalClient::class, function ($app) {
            $client = new ExternalClient;

            // Decorate with exception handling
            $client = new HttpClientExceptionDecorator($client);

            // Decorate with request logging
            $client = new RequestLoggerDecorator($client);

            return $client;
        });

        // Register SpotifyClient
        $this->app->singleton(SpotifyClient::class, function ($app) {
            return new SpotifyClient(
                config('services.spotify.access_token')
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
