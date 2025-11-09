<?php

namespace App\Services;

use App\Contracts\HttpClientInterface;
use App\Enums\HttpMethod;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

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
    public function request(?HttpMethod $method = null, ?string $path = null, array $options = []): mixed
    {
        return $this->client->request();
    }
}
