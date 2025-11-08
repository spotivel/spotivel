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
     * Make a GET request to the Spotify API.
     */
    protected function get(string $endpoint, array $query = []): mixed
    {
        return $this->client->get($endpoint, $query);
    }

    /**
     * Make a POST request to the Spotify API.
     */
    protected function post(string $endpoint, array $data = []): mixed
    {
        return $this->client->post($endpoint, $data);
    }

    /**
     * Make a PUT request to the Spotify API.
     */
    protected function put(string $endpoint, array $data = []): mixed
    {
        return $this->client->put($endpoint, $data);
    }

    /**
     * Make a DELETE request to the Spotify API.
     */
    protected function delete(string $endpoint): mixed
    {
        return $this->client->delete($endpoint);
    }
}
