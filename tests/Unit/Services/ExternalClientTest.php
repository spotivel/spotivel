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

    public function test_can_set_base_url(): void
    {
        $client = new ExternalClient();
        $client->setBaseUrl('https://api.example.com');
        
        $this->assertInstanceOf(ExternalClient::class, $client);
    }

    public function test_can_set_headers(): void
    {
        $client = new ExternalClient();
        $result = $client->setHeaders(['Authorization' => 'Bearer token']);
        
        $this->assertInstanceOf(ExternalClient::class, $result);
    }

    public function test_can_set_timeout(): void
    {
        $client = new ExternalClient();
        $result = $client->setTimeout(60);
        
        $this->assertInstanceOf(ExternalClient::class, $result);
    }

    public function test_external_client_has_http_methods(): void
    {
        $client = new ExternalClient();
        
        $this->assertTrue(method_exists($client, 'get'));
        $this->assertTrue(method_exists($client, 'post'));
        $this->assertTrue(method_exists($client, 'put'));
        $this->assertTrue(method_exists($client, 'delete'));
    }
}
