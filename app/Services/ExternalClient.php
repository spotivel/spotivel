<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ExternalClient
{
    protected string $baseUrl;
    protected array $headers = [];
    protected int $timeout = 30;

    /**
     * Create a new external client instance.
     */
    public function __construct(string $baseUrl = '', array $headers = [])
    {
        $this->baseUrl = $baseUrl;
        $this->headers = $headers;
    }

    /**
     * Set the base URL for the client.
     */
    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * Set headers for the client.
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set the timeout for requests.
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Get a configured HTTP client instance.
     */
    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders($this->headers)
            ->timeout($this->timeout);
    }

    /**
     * Make a GET request.
     */
    public function get(string $url, array $query = []): mixed
    {
        return $this->client()->get($url, $query)->json();
    }

    /**
     * Make a POST request.
     */
    public function post(string $url, array $data = []): mixed
    {
        return $this->client()->post($url, $data)->json();
    }

    /**
     * Make a PUT request.
     */
    public function put(string $url, array $data = []): mixed
    {
        return $this->client()->put($url, $data)->json();
    }

    /**
     * Make a DELETE request.
     */
    public function delete(string $url): mixed
    {
        return $this->client()->delete($url)->json();
    }
}
