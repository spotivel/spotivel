<?php

namespace App\Services\Decorators;

use App\Contracts\HttpClientInterface;
use Illuminate\Http\Client\PendingRequest;

class DynamicHeaderDecorator implements HttpClientInterface
{
    /**
     * Create a new dynamic header decorator instance.
     */
    public function __construct(
        protected HttpClientInterface $client,
        protected array $additionalHeaders = []
    ) {}

    /**
     * Get a configured HTTP request instance with additional headers.
     */
    public function request(): PendingRequest
    {
        $request = $this->client->request();

        // Early return if no additional headers
        if (empty($this->additionalHeaders)) {
            return $request;
        }

        return $request->withHeaders($this->additionalHeaders);
    }

    /**
     * Set additional headers (returns new instance for immutability).
     */
    public function withAdditionalHeaders(array $headers): self
    {
        return new self($this->client, array_merge($this->additionalHeaders, $headers));
    }
}
