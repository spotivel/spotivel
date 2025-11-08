<?php

namespace App\Services;

class SpotifyClient
{
    protected ExternalClient $client;
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
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ]);
        return $this;
    }

    /**
     * Get the underlying external client.
     */
    public function getClient(): ExternalClient
    {
        return $this->client;
    }

    /**
     * Get a configured HTTP request instance for Spotify API.
     */
    public function request(): \Illuminate\Http\Client\PendingRequest
    {
        return $this->client->request();
    }
}
