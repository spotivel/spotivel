<?php

namespace App\Services;

use App\Contracts\HttpClientInterface;
use App\Enums\HttpMethod;

class SpotifyClient
{
    protected HttpClientInterface $client;

    protected string $accessToken;

    /**
     * Create a new Spotify client instance.
     */
    public function __construct(?string $accessToken = null)
    {
        $this->client = new ExternalClient('https://api.spotify.com/v1');
        $this->accessToken = $accessToken ?? config('services.spotify.access_token', '');

        if ($this->accessToken) {
            $this->setAccessToken($this->accessToken);
        }
    }

    /**
     * Set the access token for Spotify API requests.
     */
    public function setAccessToken(string $token): self
    {
        $this->accessToken = $token;
        $this->client->setHeaders([
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ]);

        return $this;
    }

    /**
     * Get the underlying external client.
     */
    public function getClient(): HttpClientInterface
    {
        return $this->client;
    }

    /**
     * Make an HTTP request using the specified method (like Guzzle).
     *
     * @param  HttpMethod  $method  The HTTP method to use
     * @param  string  $uri  The URI to request
     * @param  array  $options  Request options (query params, body, etc.)
     */
    public function request(HttpMethod $method, string $uri, array $options = []): mixed
    {
        $pendingRequest = $this->client->request();

        return match ($method) {
            HttpMethod::GET => $pendingRequest->get($uri, $options['query'] ?? [])->json(),
            HttpMethod::POST => $pendingRequest->post($uri, $options['body'] ?? $options)->json(),
            HttpMethod::PUT => $pendingRequest->put($uri, $options['body'] ?? $options)->json(),
            HttpMethod::DELETE => $pendingRequest->delete($uri, $options['body'] ?? [])->json(),
            HttpMethod::PATCH => $pendingRequest->patch($uri, $options['body'] ?? $options)->json(),
        };
    }
}
