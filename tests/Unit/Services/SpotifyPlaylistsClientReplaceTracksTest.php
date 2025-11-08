<?php

namespace Tests\Unit\Services;

use App\Contracts\HttpClientInterface;
use App\Services\SpotifyPlaylistsClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Mockery;
use Tests\TestCase;

class SpotifyPlaylistsClientReplaceTracksTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_has_replace_tracks_method(): void
    {
        // Arrange
        $httpClient = Mockery::mock(HttpClientInterface::class);
        $client = new SpotifyPlaylistsClient($httpClient);

        // Assert
        $this->assertTrue(method_exists($client, 'replaceTracks'));
    }

    /** @test */
    public function it_replaces_tracks_in_playlist(): void
    {
        // Arrange
        $trackUris = [
            'spotify:track:track1',
            'spotify:track:track2',
            'spotify:track:track3',
        ];

        $mockResponse = ['snapshot_id' => 'snapshot123'];

        $pendingRequest = Mockery::mock(PendingRequest::class);
        $response = Mockery::mock(Response::class);

        $response->shouldReceive('json')
            ->once()
            ->andReturn($mockResponse);

        $pendingRequest->shouldReceive('put')
            ->once()
            ->with('/playlists/playlist123/tracks', [
                'uris' => $trackUris,
            ])
            ->andReturn($response);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('request')
            ->once()
            ->andReturn($pendingRequest);

        $client = new SpotifyPlaylistsClient($httpClient);

        // Act
        $result = $client->replaceTracks('playlist123', $trackUris);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('snapshot123', $result['snapshot_id']);
    }

    /** @test */
    public function it_handles_large_track_lists_with_chunking(): void
    {
        // Arrange - Create 150 track URIs (more than 100 limit)
        $trackUris = [];
        for ($i = 1; $i <= 150; $i++) {
            $trackUris[] = "spotify:track:track{$i}";
        }

        $firstChunk = array_slice($trackUris, 0, 100);
        $secondChunk = array_slice($trackUris, 100, 50);

        $mockResponse = ['snapshot_id' => 'snapshot123'];

        $pendingRequest = Mockery::mock(PendingRequest::class);
        $putResponse = Mockery::mock(Response::class);
        $postResponse = Mockery::mock(Response::class);

        $putResponse->shouldReceive('json')
            ->once()
            ->andReturn($mockResponse);

        $postResponse->shouldReceive('json')
            ->once()
            ->andReturn(['snapshot_id' => 'snapshot456']);

        // First chunk uses PUT to replace
        $pendingRequest->shouldReceive('put')
            ->once()
            ->with('/playlists/playlist123/tracks', [
                'uris' => $firstChunk,
            ])
            ->andReturn($putResponse);

        // Second chunk uses POST to add
        $pendingRequest->shouldReceive('post')
            ->once()
            ->with('/playlists/playlist123/tracks', [
                'uris' => $secondChunk,
            ])
            ->andReturn($postResponse);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('request')
            ->times(2)
            ->andReturn($pendingRequest);

        $client = new SpotifyPlaylistsClient($httpClient);

        // Act
        $result = $client->replaceTracks('playlist123', $trackUris);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('snapshot123', $result['snapshot_id']);
    }

    /** @test */
    public function it_handles_empty_track_list(): void
    {
        // Arrange
        $trackUris = [];
        $mockResponse = ['snapshot_id' => 'snapshot123'];

        $pendingRequest = Mockery::mock(PendingRequest::class);
        $response = Mockery::mock(Response::class);

        $response->shouldReceive('json')
            ->once()
            ->andReturn($mockResponse);

        $pendingRequest->shouldReceive('put')
            ->once()
            ->with('/playlists/playlist123/tracks', [
                'uris' => [],
            ])
            ->andReturn($response);

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('request')
            ->once()
            ->andReturn($pendingRequest);

        $client = new SpotifyPlaylistsClient($httpClient);

        // Act
        $result = $client->replaceTracks('playlist123', $trackUris);

        // Assert
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_replaces_exactly_100_tracks_without_chunking(): void
    {
        // Arrange - Exactly 100 tracks should not trigger chunking
        $trackUris = [];
        for ($i = 1; $i <= 100; $i++) {
            $trackUris[] = "spotify:track:track{$i}";
        }

        $mockResponse = ['snapshot_id' => 'snapshot123'];

        $pendingRequest = Mockery::mock(PendingRequest::class);
        $response = Mockery::mock(Response::class);

        $response->shouldReceive('json')
            ->once()
            ->andReturn($mockResponse);

        $pendingRequest->shouldReceive('put')
            ->once()
            ->andReturn($response);

        // Should NOT receive POST (no second chunk)
        $pendingRequest->shouldNotReceive('post');

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('request')
            ->once()
            ->andReturn($pendingRequest);

        $client = new SpotifyPlaylistsClient($httpClient);

        // Act
        $result = $client->replaceTracks('playlist123', $trackUris);

        // Assert
        $this->assertIsArray($result);
    }
}
