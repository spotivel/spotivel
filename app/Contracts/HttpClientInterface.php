<?php

namespace App\Contracts;

use Illuminate\Http\Client\PendingRequest;

interface HttpClientInterface
{
    /**
     * Get a configured HTTP request instance.
     */
    public function request(): PendingRequest;

    /**
     * Set the base URL for the client.
     */
    public function setBaseUrl(string $url): self;

    /**
     * Set headers for the client.
     */
    public function setHeaders(array $headers): self;

    /**
     * Set the timeout for requests.
     */
    public function setTimeout(int $timeout): self;
}
