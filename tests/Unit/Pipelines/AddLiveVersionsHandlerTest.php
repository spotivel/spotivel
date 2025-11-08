<?php

namespace Tests\Unit\Pipelines;

use App\DTOs\PlaylistSyncDTO;
use App\Pipelines\AddLiveVersionsHandler;
use App\Services\ExternalClient;
use App\Services\SpotifyTracksClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AddLiveVersionsHandlerTest extends TestCase
{
    protected AddLiveVersionsHandler $handler;
    protected SpotifyTracksClient $tracksClient;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = new ExternalClient(
            baseUrl: 'https://api.spotify.com/v1',
            headers: ['Authorization' => 'Bearer test-token']
        );

        $this->tracksClient = new SpotifyTracksClient($httpClient);
        $this->handler = new AddLiveVersionsHandler($this->tracksClient);
    }

    /** @test */
    public function it_adds_live_versions_to_track_collection(): void
    {
        // Arrange
        Http::fake([
            'https://api.spotify.com/v1/search*' => Http::response([
                'tracks' => [
                    'items' => [
                        ['id' => 'live1', 'name' => 'Test Song - Live'],
                        ['id' => 'live2', 'name' => 'Test Song (Live at Venue)'],
                    ],
                ],
            ], 200),
        ]);

        $originalTrack = [
            'id' => 'track123',
            'name' => 'Test Song',
            'artists' => [
                ['name' => 'Test Artist'],
            ],
        ];

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$originalTrack]),
            metadata: []
        );

        // Act
        $result = $this->handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertInstanceOf(PlaylistSyncDTO::class, $result);
        $this->assertCount(3, $result->data()); // 1 original + 2 live versions
        $this->assertEquals('track123', $result->data()[0]['id']);
        $this->assertEquals('live1', $result->data()[1]['id']);
        $this->assertEquals('live2', $result->data()[2]['id']);
    }

    /** @test */
    public function it_handles_empty_track_collection(): void
    {
        // Arrange
        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([]),
            metadata: []
        );

        // Act
        $result = $this->handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertInstanceOf(PlaylistSyncDTO::class, $result);
        $this->assertCount(0, $result->data());
    }

    /** @test */
    public function it_skips_tracks_without_name(): void
    {
        // Arrange
        $trackWithoutName = [
            'id' => 'track123',
            'artists' => [
                ['name' => 'Test Artist'],
            ],
        ];

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$trackWithoutName]),
            metadata: []
        );

        // Act
        $result = $this->handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertCount(1, $result->data()); // Only original track
        Http::assertNothingSent();
    }

    /** @test */
    public function it_skips_tracks_without_artist(): void
    {
        // Arrange
        $trackWithoutArtist = [
            'id' => 'track123',
            'name' => 'Test Song',
            'artists' => [],
        ];

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$trackWithoutArtist]),
            metadata: []
        );

        // Act
        $result = $this->handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertCount(1, $result->data()); // Only original track
        Http::assertNothingSent();
    }

    /** @test */
    public function it_filters_out_duplicate_live_versions_by_spotify_id(): void
    {
        // Arrange
        Http::fake([
            'https://api.spotify.com/v1/search*' => Http::response([
                'tracks' => [
                    'items' => [
                        ['id' => 'track123', 'name' => 'Test Song'], // Same spotify_id as original
                        ['id' => 'live2', 'name' => 'Test Song (Live at Venue)'],
                    ],
                ],
            ], 200),
        ]);

        $originalTrack = [
            'id' => 'track123',
            'name' => 'Test Song',
            'artists' => [['name' => 'Test Artist']],
        ];

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$originalTrack]),
            metadata: []
        );

        // Act
        $result = $this->handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertCount(2, $result->data()); // 1 original + 1 unique live version
        $this->assertEquals('track123', $result->data()[0]['id']);
        $this->assertEquals('live2', $result->data()[1]['id']);
    }

    /** @test */
    public function it_continues_processing_on_search_error(): void
    {
        // Arrange
        Http::fake([
            'https://api.spotify.com/v1/search*' => Http::sequence()
                ->push(null, 500) // First request fails
                ->push([
                    'tracks' => [
                        'items' => [
                            ['id' => 'live2', 'name' => 'Song 2 - Live'],
                        ],
                    ],
                ], 200), // Second request succeeds
        ]);

        $track1 = [
            'id' => 'track1',
            'name' => 'Song 1',
            'artists' => [['name' => 'Artist 1']],
        ];

        $track2 = [
            'id' => 'track2',
            'name' => 'Song 2',
            'artists' => [['name' => 'Artist 2']],
        ];

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$track1, $track2]),
            metadata: []
        );

        // Act
        $result = $this->handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertCount(3, $result->data()); // 2 original + 1 live version
        $this->assertEquals('track1', $result->data()[0]['id']);
        $this->assertEquals('track2', $result->data()[1]['id']);
        $this->assertEquals('live2', $result->data()[2]['id']);
    }

    /** @test */
    public function it_processes_multiple_tracks_with_live_versions(): void
    {
        // Arrange
        Http::fake([
            '*artist%3A%22Artist+1%22*' => Http::response([
                'tracks' => [
                    'items' => [
                        ['id' => 'live1a', 'name' => 'Song 1 - Live'],
                        ['id' => 'live1b', 'name' => 'Song 1 (Live)'],
                    ],
                ],
            ], 200),
            '*artist%3A%22Artist+2%22*' => Http::response([
                'tracks' => [
                    'items' => [
                        ['id' => 'live2a', 'name' => 'Song 2 - Live'],
                    ],
                ],
            ], 200),
        ]);

        $track1 = [
            'id' => 'track1',
            'name' => 'Song 1',
            'artists' => [['name' => 'Artist 1']],
        ];

        $track2 = [
            'id' => 'track2',
            'name' => 'Song 2',
            'artists' => [['name' => 'Artist 2']],
        ];

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$track1, $track2]),
            metadata: []
        );

        // Act
        $result = $this->handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertCount(5, $result->data()); // 2 original + 3 live versions
    }
}
