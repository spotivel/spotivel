<?php

namespace App\Contracts;

use Illuminate\Http\Client\PendingRequest;

interface HttpClientInterface
{
    /**
     * Get a configured HTTP request instance.
     */
    public function request(): PendingRequest;
}
