<?php

namespace Tests\Unit\Services;

use App\Contracts\HttpClientInterface;
use App\Services\SpotifyTracksClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Mockery;
use Tests\TestCase;

class SpotifyTracksClientSearchTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    /** @test */
    public function it_has_search_method(): void
    {
        // Arrange
        $httpClient = Mockery::mock(HttpClientInterface::class);
        $client = new SpotifyTracksClient($httpClient);

        // Assert
        $this->assertTrue(method_exists($client, 'search'));
    }

    /** @test */
    /** @test */
    /** @test */
    public function it_searches_for_tracks_with_query(): void
    {
        // Arrange
        $mockResponse = [
            'tracks' => [
                'items' => [
                    ['id' => 'track1', 'name' => 'Test Track'],
                    ['id' => 'track2', 'name' => 'Another Track'],
                ],
            ],
        ];

        $pendingRequest = Mockery::mock(PendingRequest::class);
        $response = Mockery::mock(Response::class);

        $response->shouldReceive('json')
            ->once()
            ->andReturn($mockResponse);

        $pendingRequest->shouldReceive('get')
            ->once()
            ->with('/search', [
                'q' => 'test query',
                'type' => 'track',
                'limit' => 20,
                'offset' => 0,
            ])
            ->andReturn($response);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('request')
            ->once()
            ->andReturn($pendingRequest);

        $client = new SpotifyTracksClient($httpClient);

        // Act
        $result = $client->search('test query');

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('track1', $result[0]['id']);
    }

    /** @test */
    /** @test */
    public function it_returns_empty_array_when_no_tracks_found(): void
    {
        // Arrange
        $mockResponse = [
            'tracks' => [
                'items' => [],
            ],
        ];

        $pendingRequest = Mockery::mock(PendingRequest::class);
        $response = Mockery::mock(Response::class);

        $response->shouldReceive('json')
            ->once()
            ->andReturn($mockResponse);

        $pendingRequest->shouldReceive('get')
            ->once()
            ->andReturn($response);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('request')
            ->once()
            ->andReturn($pendingRequest);

        $client = new SpotifyTracksClient($httpClient);

        // Act
        $result = $client->search('nonexistent query');

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_has_search_live_versions_method(): void
    {
        // Arrange
        $httpClient = Mockery::mock(HttpClientInterface::class);
        $client = new SpotifyTracksClient($httpClient);

        // Assert
        $this->assertTrue(method_exists($client, 'searchLiveVersions'));
    }

    /** @test */
    public function it_searches_for_live_versions_of_track(): void
    {
        // Arrange
        $mockResponse = [
            'tracks' => [
                'items' => [
                    ['id' => 'live1', 'name' => 'Test Track - Live'],
                    ['id' => 'live2', 'name' => 'Test Track (Live at Venue)'],
                ],
            ],
        ];

        $pendingRequest = Mockery::mock(PendingRequest::class);
        $response = Mockery::mock(Response::class);

        $response->shouldReceive('json')
            ->once()
            ->andReturn($mockResponse);

        $pendingRequest->shouldReceive('get')
            ->once()
            ->with('/search', [
                'q' => 'track:"Test Track" artist:"Test Artist" live',
                'type' => 'track',
                'limit' => 2,
                'offset' => 0,
            ])
            ->andReturn($response);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('request')
            ->once()
            ->andReturn($pendingRequest);

        $client = new SpotifyTracksClient($httpClient);

        // Act
        $result = $client->searchLiveVersions('Test Track', 'Test Artist', 2);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('live1', $result[0]['id']);
    }

    /** @test */
    public function it_limits_live_version_search_results(): void
    {
        // Arrange
        $mockResponse = [
            'tracks' => [
                'items' => [
                    ['id' => 'live1', 'name' => 'Test Track - Live'],
                ],
            ],
        ];

        $pendingRequest = Mockery::mock(PendingRequest::class);
        $response = Mockery::mock(Response::class);

        $response->shouldReceive('json')
            ->once()
            ->andReturn($mockResponse);

        $pendingRequest->shouldReceive('get')
            ->once()
            ->with('/search', Mockery::on(function ($params) {
                return $params['limit'] === 1;
            }))
            ->andReturn($response);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('request')
            ->once()
            ->andReturn($pendingRequest);

        $client = new SpotifyTracksClient($httpClient);

        // Act
        $result = $client->searchLiveVersions('Test Track', 'Test Artist', 1);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }
}
