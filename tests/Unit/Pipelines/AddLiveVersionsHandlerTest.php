<?php

namespace Tests\Unit\Pipelines;

use App\DTOs\PlaylistSyncDTO;
use App\Pipelines\AddLiveVersionsHandler;
use App\Services\SpotifyTracksClient;
use Mockery;
use Tests\TestCase;

class AddLiveVersionsHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_adds_live_versions_to_track_collection(): void
    {
        // Arrange
        $tracksClient = Mockery::mock(SpotifyTracksClient::class);
        $handler = new AddLiveVersionsHandler($tracksClient);

        $originalTrack = [
            'id' => 'track123',
            'name' => 'Test Song',
            'artists' => [
                ['name' => 'Test Artist'],
            ],
        ];

        $liveVersions = [
            [
                'id' => 'live1',
                'name' => 'Test Song - Live',
                'artists' => [
                    ['name' => 'Test Artist'],
                ],
            ],
            [
                'id' => 'live2',
                'name' => 'Test Song (Live at Venue)',
                'artists' => [
                    ['name' => 'Test Artist'],
                ],
            ],
        ];

        $tracksClient->shouldReceive('searchLiveVersions')
            ->once()
            ->with('Test Song', 'Test Artist', 2)
            ->andReturn($liveVersions);

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$originalTrack]),
            metadata: []
        );

        // Act
        $result = $handler->handle($dto, fn ($dto) => $dto);

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
        $tracksClient = Mockery::mock(SpotifyTracksClient::class);
        $handler = new AddLiveVersionsHandler($tracksClient);

        $tracksClient->shouldNotReceive('searchLiveVersions');

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([]),
            metadata: []
        );

        // Act
        $result = $handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertInstanceOf(PlaylistSyncDTO::class, $result);
        $this->assertCount(0, $result->data());
    }

    /** @test */
    public function it_skips_tracks_without_name(): void
    {
        // Arrange
        $tracksClient = Mockery::mock(SpotifyTracksClient::class);
        $handler = new AddLiveVersionsHandler($tracksClient);

        $trackWithoutName = [
            'id' => 'track123',
            'artists' => [
                ['name' => 'Test Artist'],
            ],
        ];

        $tracksClient->shouldNotReceive('searchLiveVersions');

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$trackWithoutName]),
            metadata: []
        );

        // Act
        $result = $handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertCount(1, $result->data()); // Only original track
    }

    /** @test */
    public function it_skips_tracks_without_artist(): void
    {
        // Arrange
        $tracksClient = Mockery::mock(SpotifyTracksClient::class);
        $handler = new AddLiveVersionsHandler($tracksClient);

        $trackWithoutArtist = [
            'id' => 'track123',
            'name' => 'Test Song',
            'artists' => [],
        ];

        $tracksClient->shouldNotReceive('searchLiveVersions');

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$trackWithoutArtist]),
            metadata: []
        );

        // Act
        $result = $handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertCount(1, $result->data()); // Only original track
    }

    /** @test */
    public function it_filters_out_duplicate_live_versions(): void
    {
        // Arrange
        $tracksClient = Mockery::mock(SpotifyTracksClient::class);
        $handler = new AddLiveVersionsHandler($tracksClient);

        $originalTrack = [
            'id' => 'track123',
            'name' => 'Test Song',
            'artists' => [
                ['name' => 'Test Artist'],
            ],
        ];

        // Live versions include the same track ID as original
        $liveVersions = [
            [
                'id' => 'track123', // Same as original
                'name' => 'Test Song',
            ],
            [
                'id' => 'live2',
                'name' => 'Test Song (Live at Venue)',
            ],
        ];

        $tracksClient->shouldReceive('searchLiveVersions')
            ->once()
            ->with('Test Song', 'Test Artist', 2)
            ->andReturn($liveVersions);

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$originalTrack]),
            metadata: []
        );

        // Act
        $result = $handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertCount(2, $result->data()); // 1 original + 1 unique live version
        $this->assertEquals('track123', $result->data()[0]['id']);
        $this->assertEquals('live2', $result->data()[1]['id']);
    }

    /** @test */
    public function it_continues_processing_on_search_error(): void
    {
        // Arrange
        $tracksClient = Mockery::mock(SpotifyTracksClient::class);
        $handler = new AddLiveVersionsHandler($tracksClient);

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

        // First search fails, second succeeds
        $tracksClient->shouldReceive('searchLiveVersions')
            ->once()
            ->with('Song 1', 'Artist 1', 2)
            ->andThrow(new \Exception('API Error'));

        $tracksClient->shouldReceive('searchLiveVersions')
            ->once()
            ->with('Song 2', 'Artist 2', 2)
            ->andReturn([
                ['id' => 'live2', 'name' => 'Song 2 - Live'],
            ]);

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$track1, $track2]),
            metadata: []
        );

        // Act
        $result = $handler->handle($dto, fn ($dto) => $dto);

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
        $tracksClient = Mockery::mock(SpotifyTracksClient::class);
        $handler = new AddLiveVersionsHandler($tracksClient);

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

        $tracksClient->shouldReceive('searchLiveVersions')
            ->once()
            ->with('Song 1', 'Artist 1', 2)
            ->andReturn([
                ['id' => 'live1a', 'name' => 'Song 1 - Live'],
                ['id' => 'live1b', 'name' => 'Song 1 (Live)'],
            ]);

        $tracksClient->shouldReceive('searchLiveVersions')
            ->once()
            ->with('Song 2', 'Artist 2', 2)
            ->andReturn([
                ['id' => 'live2a', 'name' => 'Song 2 - Live'],
            ]);

        $dto = new PlaylistSyncDTO(
            playlistId: 1,
            spotifyId: 'playlist123',
            tracks: collect([$track1, $track2]),
            metadata: []
        );

        // Act
        $result = $handler->handle($dto, fn ($dto) => $dto);

        // Assert
        $this->assertCount(5, $result->data()); // 2 original + 3 live versions
    }
}
