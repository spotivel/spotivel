<?php

namespace App\Services;

use App\Contracts\HttpClientInterface;
use App\Enums\HttpMethod;
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
    public function request(?HttpMethod $method = null, ?string $path = null, array $options = []): mixed
    {
        // If no method provided, return PendingRequest for chaining
        if ($method === null) {
            return $this->client->request();
        }

        // Execute the request with the given method and options
        $request = $this->client->request();

        return match ($method) {
            HttpMethod::GET => $request->get($path, $options['query'] ?? []),
            HttpMethod::POST => $request->post($path, $options['json'] ?? $options['body'] ?? []),
            HttpMethod::PUT => $request->put($path, $options['json'] ?? $options['body'] ?? []),
            HttpMethod::DELETE => $request->delete($path, $options['json'] ?? $options['body'] ?? []),
            HttpMethod::PATCH => $request->patch($path, $options['json'] ?? $options['body'] ?? []),
        };
    }

    /**
     * Make a request with additional headers.
     */
    public function requestWithHeaders(array $headers): PendingRequest
    {
        return $this->client->request()->withHeaders($headers);
    }
}
