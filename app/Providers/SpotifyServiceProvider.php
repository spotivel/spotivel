<?php

namespace App\Providers;

use App\Contracts\OAuthServiceInterface;
use App\Services\Decorators\HttpClientExceptionDecorator;
use App\Services\Decorators\RequestLoggerDecorator;
use App\Services\ExternalClient;
use App\Services\SpotifyClient;
use App\Services\SpotifyOAuthService;
use Illuminate\Support\ServiceProvider;

class SpotifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register factory for creating decorated HTTP clients
        $this->app->bind('spotify.http.factory', function ($app) {
            return function (string $baseUrl = '', array $headers = [], int $timeout = 30) {
                $client = new ExternalClient(
                    baseUrl: $baseUrl,
                    headers: $headers,
                    timeout: $timeout
                );

                // Decorate with exception handling
                $client = new HttpClientExceptionDecorator($client);

                // Decorate with request logging
                $client = new RequestLoggerDecorator($client);

                return $client;
            };
        });

        // Register SpotifyOAuthService
        $this->app->singleton(OAuthServiceInterface::class, function ($app) {
            $factory = $app->make('spotify.http.factory');

            return new SpotifyOAuthService(
                client: $factory('', [], 30),
                clientId: config('services.spotify.client_id'),
                clientSecret: config('services.spotify.client_secret'),
                redirectUri: config('services.spotify.redirect'),
                scopes: config('services.spotify.scopes'),
                authUrl: config('services.spotify.auth_url'),
                tokenUrl: config('services.spotify.token_url')
            );
        });

        // Register ExternalClient configured for Spotify with decorators
        $this->app->bind(ExternalClient::class, function ($app) {
            $factory = $app->make('spotify.http.factory');

            // Try to get token from cache first, fallback to config
            $oauthService = $app->make(OAuthServiceInterface::class);
            $accessToken = $oauthService->getCachedToken() ?? config('services.spotify.access_token', '');

            return $factory(
                'https://api.spotify.com/v1',
                [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                30
            );
        });

        // Register SpotifyClient with configured ExternalClient
        $this->app->bind(SpotifyClient::class, function ($app) {
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
