<?php

namespace Tests\Unit\Services;

use App\Services\ExternalClient;
use PHPUnit\Framework\TestCase;

class ExternalClientTest extends TestCase
{
    public function test_can_create_external_client(): void
    {
        $client = new ExternalClient('https://api.example.com');

        $this->assertInstanceOf(ExternalClient::class, $client);
    }

    public function test_can_create_with_base_url(): void
    {
        $client = new ExternalClient(
            baseUrl: 'https://api.example.com'
        );

        $this->assertInstanceOf(ExternalClient::class, $client);
    }

    public function test_can_create_with_headers(): void
    {
        $client = new ExternalClient(
            baseUrl: 'https://api.example.com',
            headers: ['Authorization' => 'Bearer token']
        );

        $this->assertInstanceOf(ExternalClient::class, $client);
    }

    public function test_can_create_with_timeout(): void
    {
        $client = new ExternalClient(
            baseUrl: 'https://api.example.com',
            timeout: 60
        );

        $this->assertInstanceOf(ExternalClient::class, $client);
    }

    public function test_external_client_has_request_method(): void
    {
        $client = new ExternalClient;

        $this->assertTrue(method_exists($client, 'request'));
    }

    public function test_can_create_with_all_parameters(): void
    {
        $client = new ExternalClient(
            baseUrl: 'https://api.example.com',
            headers: [
                'Authorization' => 'Bearer token',
                'Content-Type' => 'application/json',
            ],
            timeout: 120
        );

        $this->assertInstanceOf(ExternalClient::class, $client);
    }
}
