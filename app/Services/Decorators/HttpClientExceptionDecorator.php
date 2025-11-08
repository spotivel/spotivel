<?php

namespace App\Services\Decorators;

use App\Contracts\HttpClientInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

class HttpClientExceptionDecorator implements HttpClientInterface
{
    public function __construct(
        protected HttpClientInterface $client
    ) {
    }

    /**
     * Set the base URL for the client.
     */
    public function setBaseUrl(string $url): self
    {
        $this->client->setBaseUrl($url);
        return $this;
    }

    /**
     * Set headers for the client.
     */
    public function setHeaders(array $headers): self
    {
        $this->client->setHeaders($headers);
        return $this;
    }

    /**
     * Set the timeout for requests.
     */
    public function setTimeout(int $timeout): self
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
