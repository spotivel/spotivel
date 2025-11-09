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
            return $this->applyHeaders($this->client->request(), $options);
        }

        // Execute request with method
        $request = $this->applyHeaders($this->client->request(), $options);

        // Handle different HTTP methods
        if ($method === HttpMethod::GET) {
            return $request->get($path, $options['query'] ?? []);
        }

        if ($method === HttpMethod::POST) {
            return $request->post($path, $options['json'] ?? $options['body'] ?? []);
        }

        if ($method === HttpMethod::PUT) {
            return $request->put($path, $options['json'] ?? $options['body'] ?? []);
        }

        if ($method === HttpMethod::DELETE) {
            return $request->delete($path, $options['json'] ?? $options['body'] ?? []);
        }

        if ($method === HttpMethod::PATCH) {
            return $request->patch($path, $options['json'] ?? $options['body'] ?? []);
        }

        return $request;
    }

    /**
     * Apply additional headers from options to the request.
     */
    private function applyHeaders(PendingRequest $request, array $options): PendingRequest
    {
        if (isset($options['headers']) && is_array($options['headers'])) {
            return $request->withHeaders($options['headers']);
        }

        return $request;
    }
}
