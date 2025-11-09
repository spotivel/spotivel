<?php

namespace Tests\Unit\Services;

use App\Services\ExternalClient;
use App\Services\SpotifyTracksClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SpotifyTracksClientSearchTest extends TestCase
{
    protected SpotifyTracksClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Create client with ExternalClient using Http facade
        $httpClient = new ExternalClient(
            baseUrl: 'https://api.spotify.com/v1',
            headers: ['Authorization' => 'Bearer test-token']
        );

        $this->client = new SpotifyTracksClient($httpClient);
    }

    /** @test */
    public function it_searches_for_tracks_with_query(): void
    {
        // Arrange
        Http::fake([
            'https://api.spotify.com/v1/search*' => Http::response([
                'tracks' => [
                    'items' => [
                        ['id' => 'track1', 'name' => 'Test Track'],
                        ['id' => 'track2', 'name' => 'Another Track'],
                    ],
                ],
            ], 200),
        ]);

        // Act
        $result = $this->client->search('test query');

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('track1', $result[0]['id']);
        $this->assertEquals('Test Track', $result[0]['name']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/search?q=test+query&type=track&limit=20&offset=0';
        });
    }

    /** @test */
    public function it_returns_empty_array_when_no_tracks_found(): void
    {
        // Arrange
        Http::fake([
            'https://api.spotify.com/v1/search*' => Http::response([
                'tracks' => [
                    'items' => [],
                ],
            ], 200),
        ]);

        // Act
        $result = $this->client->search('nonexistent query');

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_searches_for_live_versions_of_track(): void
    {
        // Arrange
        Http::fake([
            'https://api.spotify.com/v1/search*' => Http::response([
                'tracks' => [
                    'items' => [
                        ['id' => 'live1', 'name' => 'Test Track - Live'],
                        ['id' => 'live2', 'name' => 'Test Track (Live at Venue)'],
                    ],
                ],
            ], 200),
        ]);

        // Act
        $result = $this->client->searchLiveVersions('Test Track', 'Test Artist', 2);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('live1', $result[0]['id']);

        Http::assertSent(function ($request) {
            $url = $request->url();

            // Artist should come first, then track
            return str_contains($url, 'artist%3A%22Test+Artist%22') &&
                   str_contains($url, 'track%3A%22Test+Track%22');
        });
    }

    /** @test */
    public function it_adds_live_keyword_if_not_present_in_track_name(): void
    {
        // Arrange
        Http::fake([
            'https://api.spotify.com/v1/search*' => Http::response([
                'tracks' => ['items' => []],
            ], 200),
        ]);

        // Act
        $this->client->searchLiveVersions('Test Track', 'Test Artist', 2);

        // Assert - should include "live" keyword
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'live');
        });
    }

    /** @test */
    public function it_does_not_duplicate_live_keyword_if_already_in_track_name(): void
    {
        // Arrange
        Http::fake([
            'https://api.spotify.com/v1/search*' => Http::response([
                'tracks' => ['items' => []],
            ], 200),
        ]);

        // Act
        $this->client->searchLiveVersions('Test Track Live', 'Test Artist', 2);

        // Assert - should not add extra "live" keyword
        Http::assertSent(function ($request) {
            $url = $request->url();

            // Count occurrences of "live" - should only appear once from track name
            return substr_count(strtolower($url), 'live') === 1;
        });
    }

    /** @test */
    public function it_limits_live_version_search_results(): void
    {
        // Arrange
        Http::fake([
            'https://api.spotify.com/v1/search*' => Http::response([
                'tracks' => [
                    'items' => [
                        ['id' => 'live1', 'name' => 'Test Track - Live'],
                    ],
                ],
            ], 200),
        ]);

        // Act
        $result = $this->client->searchLiveVersions('Test Track', 'Test Artist', 1);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'limit=1');
        });
    }
}
