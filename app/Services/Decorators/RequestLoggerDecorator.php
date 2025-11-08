<?php

namespace App\Services\Decorators;

use App\Contracts\HttpClientInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

class RequestLoggerDecorator implements HttpClientInterface
{
    public function __construct(
        protected HttpClientInterface $client
    ) {}

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
     * Get a configured HTTP request instance with request logging.
     */
    public function request(): PendingRequest
    {
        return $this->client->request()
            ->beforeSending(function ($request, $options) {
                Log::info('HTTP Request', [
                    'method' => $request->method(),
                    'url' => $request->url(),
                    'headers' => $request->headers(),
                ]);
            })
            ->onSuccess(function ($response) {
                Log::info('HTTP Response Success', [
                    'status' => $response->status(),
                    'url' => $response->effectiveUri(),
                ]);
            })
            ->onError(function ($response) {
                Log::error('HTTP Response Error', [
                    'status' => $response->status(),
                    'url' => $response->effectiveUri(),
                    'body' => $response->body(),
                ]);
            });
    }
}
