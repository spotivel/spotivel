<?php

namespace Tests\Unit\Services;

use App\Services\ExternalClient;
use App\Services\SpotifyPlaylistsClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SpotifyPlaylistsClientReplaceTracksTest extends TestCase
{
    protected SpotifyPlaylistsClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = new ExternalClient(
            baseUrl: 'https://api.spotify.com/v1',
            headers: ['Authorization' => 'Bearer test-token']
        );

        $this->client = new SpotifyPlaylistsClient($httpClient);
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

        Http::fake([
            'https://api.spotify.com/v1/playlists/playlist123/tracks*' => Http::response([
                'snapshot_id' => 'snapshot123',
            ], 200),
        ]);

        // Act
        $result = $this->client->replaceTracks('playlist123', $trackUris);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('snapshot123', $result['snapshot_id']);

        Http::assertSent(function ($request) {
            return $request->method() === 'PUT' &&
                   str_contains($request->url(), 'playlists/playlist123/tracks');
        });
    }

    /** @test */
    public function it_handles_large_track_lists_with_chunking(): void
    {
        // Arrange - Create 150 track URIs (more than 100 limit)
        $trackUris = array_map(fn ($i) => "spotify:track:track{$i}", range(1, 150));

        Http::fake([
            'https://api.spotify.com/v1/playlists/playlist123/tracks*' => Http::response([
                'snapshot_id' => 'snapshot123',
            ], 200),
        ]);

        // Act
        $result = $this->client->replaceTracks('playlist123', $trackUris);

        // Assert
        $this->assertIsArray($result);

        // Should have 1 PUT request (first chunk) + 1 POST request (second chunk)
        Http::assertSentCount(2);

        Http::assertSent(function ($request) {
            return $request->method() === 'PUT';
        });

        Http::assertSent(function ($request) {
            return $request->method() === 'POST';
        });
    }

    /** @test */
    public function it_handles_empty_track_list(): void
    {
        // Arrange
        Http::fake([
            'https://api.spotify.com/v1/playlists/playlist123/tracks*' => Http::response([
                'snapshot_id' => 'snapshot123',
            ], 200),
        ]);

        // Act
        $result = $this->client->replaceTracks('playlist123', []);

        // Assert
        $this->assertIsArray($result);

        Http::assertSent(function ($request) use (&$requestBody) {
            $requestBody = $request->data();

            return $request->method() === 'PUT' &&
                   isset($requestBody['uris']) &&
                   empty($requestBody['uris']);
        });
    }

    /** @test */
    public function it_replaces_exactly_100_tracks_without_chunking(): void
    {
        // Arrange - Exactly 100 tracks should not trigger chunking
        $trackUris = array_map(fn ($i) => "spotify:track:track{$i}", range(1, 100));

        Http::fake([
            'https://api.spotify.com/v1/playlists/playlist123/tracks*' => Http::response([
                'snapshot_id' => 'snapshot123',
            ], 200),
        ]);

        // Act
        $result = $this->client->replaceTracks('playlist123', $trackUris);

        // Assert
        $this->assertIsArray($result);

        // Should only have 1 PUT request, no POST
        Http::assertSentCount(1);

        Http::assertSent(function ($request) {
            return $request->method() === 'PUT';
        });
    }

    /** @test */
    public function it_uses_json_format_for_request_body(): void
    {
        // Arrange
        $trackUris = ['spotify:track:track1'];

        Http::fake([
            'https://api.spotify.com/v1/playlists/playlist123/tracks*' => Http::response([
                'snapshot_id' => 'snapshot123',
            ], 200),
        ]);

        // Act
        $this->client->replaceTracks('playlist123', $trackUris);

        // Assert
        Http::assertSent(function ($request) {
            $data = $request->data();

            return isset($data['uris']) &&
                   is_array($data['uris']) &&
                   $data['uris'][0] === 'spotify:track:track1';
        });
    }
}
