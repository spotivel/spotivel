<?php

namespace App\Services\Decorators;

use App\Contracts\HttpClientInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

class HttpClientExceptionDecorator implements HttpClientInterface
{
    public function __construct(
        protected HttpClientInterface $client
    ) {}

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
