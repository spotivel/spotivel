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
        // Register OAuth HTTP client for Spotify token endpoints
        $this->app->singleton('spotify.oauth.client', function ($app) {
            $client = new ExternalClient(
                baseUrl: '',
                headers: [],
                timeout: 30
            );

            // Decorate with exception handling
            $client = new HttpClientExceptionDecorator($client);

            // Decorate with request logging
            $client = new RequestLoggerDecorator($client);

            return $client;
        });

        // Register SpotifyOAuthService
        $this->app->singleton(OAuthServiceInterface::class, function ($app) {
            return new SpotifyOAuthService(
                client: $app->make('spotify.oauth.client'),
                clientId: config('services.spotify.client_id'),
                clientSecret: config('services.spotify.client_secret'),
                redirectUri: config('services.spotify.redirect'),
                scopes: config('services.spotify.scopes'),
                authUrl: config('services.spotify.auth_url'),
                tokenUrl: config('services.spotify.token_url')
            );
        });

        // Register ExternalClient configured for Spotify with decorators
        $this->app->singleton(ExternalClient::class, function ($app) {
            // Try to get token from cache first, fallback to config
            $oauthService = $app->make(OAuthServiceInterface::class);
            $accessToken = $oauthService->getCachedToken() ?? config('services.spotify.access_token', '');

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
