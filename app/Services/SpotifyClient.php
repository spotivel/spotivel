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
        // If no method provided, return PendingRequest for chaining
        if ($method === null) {
            return $this->client->request();
        }

        // Handle different HTTP methods
        if ($method === HttpMethod::GET) {
            return $this->get($path, $options);
        }

        if ($method === HttpMethod::POST) {
            return $this->post($path, $options);
        }

        if ($method === HttpMethod::PUT) {
            return $this->put($path, $options);
        }

        if ($method === HttpMethod::DELETE) {
            return $this->delete($path, $options);
        }

        if ($method === HttpMethod::PATCH) {
            return $this->patch($path, $options);
        }

        return $this->client->request();
    }

    /**
     * Make a GET request.
     */
    public function get(string $path, array $options = []): Response
    {
        return $this->client->request()->get($path, $options['query'] ?? []);
    }

    /**
     * Make a POST request.
     */
    public function post(string $path, array $options = []): Response
    {
        return $this->client->request()->post($path, $options['json'] ?? $options['body'] ?? []);
    }

    /**
     * Make a PUT request.
     */
    public function put(string $path, array $options = []): Response
    {
        return $this->client->request()->put($path, $options['json'] ?? $options['body'] ?? []);
    }

    /**
     * Make a DELETE request.
     */
    public function delete(string $path, array $options = []): Response
    {
        return $this->client->request()->delete($path, $options['json'] ?? $options['body'] ?? []);
    }

    /**
     * Make a PATCH request.
     */
    public function patch(string $path, array $options = []): Response
    {
        return $this->client->request()->patch($path, $options['json'] ?? $options['body'] ?? []);
    }

    /**
     * Make a request with additional headers.
     */
    public function requestWithHeaders(array $headers): PendingRequest
    {
        return $this->client->request()->withHeaders($headers);
    }

    /**
     * Set additional headers (returns new instance for immutability).
     */
    public function withAdditionalHeaders(array $headers): PendingRequest
    {
        return $this->client->request()->withHeaders($headers);
    }
}
