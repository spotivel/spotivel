<?php

namespace App\Services;

use App\Contracts\HttpClientInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ExternalClient implements HttpClientInterface
{
    /**
     * Create a new external client instance.
     */
    public function __construct(
        protected string $baseUrl = '',
        protected array $headers = [],
        protected int $timeout = 30
    ) {}

    /**
     * Get a configured HTTP request instance with automatic exception throwing.
     */
    public function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders($this->headers)
            ->timeout($this->timeout)
            ->throw();
    }
}
