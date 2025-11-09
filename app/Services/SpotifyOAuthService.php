<?php

namespace App\Services;

use App\Contracts\OAuthServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SpotifyOAuthService implements OAuthServiceInterface
{
    /**
     * Create a new Spotify OAuth service instance.
     */
    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected string $redirectUri,
        protected string $authUrl = 'https://accounts.spotify.com/authorize',
        protected string $tokenUrl = 'https://accounts.spotify.com/api/token'
    ) {}

    /**
     * Get the authorization URL for OAuth flow.
     */
    public function getAuthorizationUrl(): string
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => 'user-library-read playlist-read-private playlist-modify-public playlist-modify-private',
        ]);

        return "{$this->authUrl}?{$params}";
    }

    /**
     * Exchange authorization code for access token.
     */
    public function getAccessToken(string $code): array
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ])->throw();

        return $response->json();
    }

    /**
     * Refresh the access token using refresh token.
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ])->throw();

        return $response->json();
    }

    /**
     * Get cached access token.
     */
    public function getCachedToken(): ?string
    {
        // Check if token exists and is not expired
        if (! Cache::has('spotify_access_token')) {
            return null;
        }

        // If we have a refresh token and the access token is about to expire, refresh it
        if (Cache::has('spotify_refresh_token') && Cache::get('spotify_token_expires_at') <= now()->addMinutes(5)->timestamp) {
            $this->attemptTokenRefresh();
        }

        return Cache::get('spotify_access_token');
    }

    /**
     * Cache the access token and refresh token.
     */
    public function cacheToken(string $accessToken, string $refreshToken, int $expiresIn): void
    {
        $expiresAt = now()->addSeconds($expiresIn)->timestamp;

        Cache::put('spotify_access_token', $accessToken, $expiresIn);
        Cache::put('spotify_refresh_token', $refreshToken, now()->addDays(30));
        Cache::put('spotify_token_expires_at', $expiresAt, now()->addDays(30));
    }

    /**
     * Clear cached tokens.
     */
    public function clearCache(): void
    {
        Cache::forget('spotify_access_token');
        Cache::forget('spotify_refresh_token');
        Cache::forget('spotify_token_expires_at');
    }

    /**
     * Attempt to refresh the access token.
     */
    protected function attemptTokenRefresh(): void
    {
        $refreshToken = Cache::get('spotify_refresh_token');

        if (! $refreshToken) {
            return;
        }

        try {
            $tokenData = $this->refreshAccessToken($refreshToken);

            // Spotify sometimes doesn't return a new refresh token
            $newRefreshToken = $tokenData['refresh_token'] ?? $refreshToken;

            $this->cacheToken(
                $tokenData['access_token'],
                $newRefreshToken,
                $tokenData['expires_in']
            );
        } catch (\Exception $e) {
            // If refresh fails, clear cache to force re-authentication
            $this->clearCache();
        }
    }
}
