<?php

namespace Tests\Unit\Services;

use App\Contracts\HttpClientInterface;
use App\Services\ExternalClient;
use App\Services\SpotifyClient;
use Mockery;
use PHPUnit\Framework\TestCase;

class SpotifyClientTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_create_spotify_client(): void
    {
        $mockClient = Mockery::mock(HttpClientInterface::class);
        $client = new SpotifyClient($mockClient);

        $this->assertInstanceOf(SpotifyClient::class, $client);
    }

    public function test_can_create_spotify_client_with_external_client(): void
    {
        $externalClient = new ExternalClient(
            baseUrl: 'https://api.spotify.com/v1',
            headers: ['Authorization' => 'Bearer test-token']
        );

        $client = new SpotifyClient($externalClient);

        $this->assertInstanceOf(SpotifyClient::class, $client);
    }

    public function test_uses_http_client_interface(): void
    {
        $mockClient = Mockery::mock(HttpClientInterface::class);
        $client = new SpotifyClient($mockClient);

        $this->assertInstanceOf(SpotifyClient::class, $client);
    }

    public function test_spotify_client_does_not_extend_external_client(): void
    {
        $mockClient = Mockery::mock(HttpClientInterface::class);
        $client = new SpotifyClient($mockClient);

        // SpotifyClient should use HttpClientInterface, not extend ExternalClient
        $this->assertNotInstanceOf(ExternalClient::class, $client);
    }

    public function test_spotify_client_has_request_method(): void
    {
        $mockClient = Mockery::mock(HttpClientInterface::class);
        $client = new SpotifyClient($mockClient);

        $this->assertTrue(method_exists($client, 'request'));
    }

    public function test_spotify_client_has_request_with_headers_method(): void
    {
        $mockClient = Mockery::mock(HttpClientInterface::class);
        $client = new SpotifyClient($mockClient);

        $this->assertTrue(method_exists($client, 'requestWithHeaders'));
    }
}
