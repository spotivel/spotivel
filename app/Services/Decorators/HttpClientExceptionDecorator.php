<?php

namespace App\Services\Decorators;

use App\Services\ExternalClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

class HttpClientExceptionDecorator extends ExternalClient
{
    public function __construct(
        protected ExternalClient $client
    ) {
        // Don't call parent constructor as we're decorating
    }

    /**
     * Set the base URL for the client.
     */
    public function setBaseUrl(string $url): ExternalClient
    {
        $this->client->setBaseUrl($url);
        return $this;
    }

    /**
     * Set headers for the client.
     */
    public function setHeaders(array $headers): ExternalClient
    {
        $this->client->setHeaders($headers);
        return $this;
    }

    /**
     * Set the timeout for requests.
     */
    public function setTimeout(int $timeout): ExternalClient
    {
        $this->client->setTimeout($timeout);
        return $this;
    }

    /**
     * Get a configured HTTP request instance with exception handling.
     */
    public function request(): PendingRequest
    {
        return $this->client->request()
            ->throw(function ($response, $e) {
                Log::error('HTTP Client Exception', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'exception' => $e->getMessage(),
                ]);
            });
    }
}
