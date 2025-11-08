<?php

namespace Tests\Unit\Services;

use App\Services\ExternalClient;
use App\Services\SpotifyClient;
use PHPUnit\Framework\TestCase;

class SpotifyClientTest extends TestCase
{
    public function test_can_create_spotify_client(): void
    {
        $client = new SpotifyClient;

        $this->assertInstanceOf(SpotifyClient::class, $client);
    }

    public function test_can_create_spotify_client_with_token(): void
    {
        $client = new SpotifyClient('test_token');

        $this->assertInstanceOf(SpotifyClient::class, $client);
    }

    public function test_can_set_access_token(): void
    {
        $client = new SpotifyClient;
        $result = $client->setAccessToken('new_token');

        $this->assertInstanceOf(SpotifyClient::class, $result);
    }

    public function test_uses_external_client(): void
    {
        $client = new SpotifyClient;
        $externalClient = $client->getClient();

        $this->assertInstanceOf(ExternalClient::class, $externalClient);
    }

    public function test_spotify_client_does_not_extend_external_client(): void
    {
        $client = new SpotifyClient;

        // SpotifyClient should use ExternalClient, not extend it
        $this->assertNotInstanceOf(ExternalClient::class, $client);
    }

    public function test_spotify_client_has_request_method(): void
    {
        $client = new SpotifyClient;

        $this->assertTrue(method_exists($client, 'request'));
    }
}
