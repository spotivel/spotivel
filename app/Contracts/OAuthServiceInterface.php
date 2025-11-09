<?php

namespace App\Contracts;

interface OAuthServiceInterface
{
    /**
     * Get the authorization URL for OAuth flow.
     */
    public function getAuthorizationUrl(): string;

    /**
     * Exchange authorization code for access token.
     */
    public function getAccessToken(string $code): array;

    /**
     * Refresh the access token using refresh token.
     */
    public function refreshAccessToken(string $refreshToken): array;

    /**
     * Get cached access token.
     */
    public function getCachedToken(): ?string;

    /**
     * Cache the access token and refresh token.
     */
    public function cacheToken(string $accessToken, string $refreshToken, int $expiresIn): void;

    /**
     * Clear cached tokens.
     */
    public function clearCache(): void;

    /**
     * Get the time until token expires in seconds.
     */
    public function getTimeUntilExpiry(): ?int;

    /**
     * Manually refresh the current token.
     */
    public function refreshCurrentToken(): bool;
}
