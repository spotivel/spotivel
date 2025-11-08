<?php

namespace App\Services;

use App\Contracts\HttpClientInterface;
use Illuminate\Http\Client\PendingRequest;

class SpotifyClient
{
    /**
     * Create a new Spotify client instance.
     */
    public function __construct(
        protected HttpClientInterface $client
    ) {}

    /**
     * Get a configured HTTP request instance.
     */
    public function request(): PendingRequest
    {
        return $this->client->request();
    }
}
