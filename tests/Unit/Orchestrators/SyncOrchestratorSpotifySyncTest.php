<?php

namespace Tests\Unit\Orchestrators;

use App\DTOs\PlaylistSyncDTO;
use App\Models\Playlist;
use App\Orchestrators\SyncOrchestrator;
use App\Services\Database\ArtistService;
use App\Services\Database\PlaylistService;
use App\Services\Database\TrackService;
use App\Services\SpotifyPlaylistsClient;
use App\Transformers\PlaylistSyncDTOTransformer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SyncOrchestratorSpotifySyncTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_syncs_to_spotify_when_enabled(): void
    {
        // Arrange
        $trackService = Mockery::mock(TrackService::class);
        $artistService = Mockery::mock(ArtistService::class);
        $playlistService = Mockery::mock(PlaylistService::class);
        $playlistsClient = Mockery::mock(SpotifyPlaylistsClient::class);
        $transformer = Mockery::mock(PlaylistSyncDTOTransformer::class);

        $playlist = Playlist::factory()->create([
            'spotify_id' => 'playlist123',
            'name' => 'Test Playlist',
        ]);

        $track1 = \App\Models\Track::factory()->create(['spotify_id' => 'track1']);
        $track2 = \App\Models\Track::factory()->create(['spotify_id' => 'track2']);

        $tracks = collect([
            ['id' => 'track1', 'name' => 'Track 1', 'uri' => 'spotify:track:track1', 'duration_ms' => 180000, 'explicit' => false, 'href' => 'https://api.spotify.com/v1/tracks/track1', 'external_urls' => ['spotify' => 'https://open.spotify.com/track/track1']],
            ['id' => 'track2', 'name' => 'Track 2', 'uri' => 'spotify:track:track2', 'duration_ms' => 200000, 'explicit' => false, 'href' => 'https://api.spotify.com/v1/tracks/track2', 'external_urls' => ['spotify' => 'https://open.spotify.com/track/track2']],
        ]);

        $dto = new PlaylistSyncDTO(
            playlistId: $playlist->id,
            spotifyId: $playlist->spotify_id,
            tracks: $tracks,
            metadata: []
        );

        $trackUris = ['spotify:track:track1', 'spotify:track:track2'];

        // Mock transformer
        $transformer->shouldReceive('tracksToSpotifyUris')
            ->once()
            ->with($dto)
            ->andReturn($trackUris);

        // Mock playlist client
        $playlistsClient->shouldReceive('replaceTracks')
            ->once()
            ->with('playlist123', $trackUris)
            ->andReturn(['snapshot_id' => 'snapshot123']);

        // Mock database services for saving
        $trackService->shouldReceive('createOrUpdate')
            ->times(2)
            ->andReturn($track1, $track2);

        $playlistService->shouldReceive('syncTracks')
            ->once();

        $orchestrator = new SyncOrchestrator(
            $trackService,
            $artistService,
            $playlistService,
            $playlistsClient,
            $transformer
        );

        // Act
        $orchestrator->setSyncToSpotify(true);
        $orchestrator->sync($dto, $playlist);

        // Assert - Mockery will verify expectations
        $this->assertTrue(true);
    }

    /** @test */
    public function it_does_not_sync_to_spotify_when_disabled(): void
    {
        // Arrange
        $trackService = Mockery::mock(TrackService::class);
        $artistService = Mockery::mock(ArtistService::class);
        $playlistService = Mockery::mock(PlaylistService::class);
        $playlistsClient = Mockery::mock(SpotifyPlaylistsClient::class);
        $transformer = Mockery::mock(PlaylistSyncDTOTransformer::class);

        $playlist = Playlist::factory()->create([
            'spotify_id' => 'playlist123',
            'name' => 'Test Playlist',
        ]);

        $track1 = \App\Models\Track::factory()->create(['spotify_id' => 'track1']);

        $tracks = collect([
            ['id' => 'track1', 'name' => 'Track 1', 'uri' => 'spotify:track:track1', 'duration_ms' => 180000, 'explicit' => false, 'href' => 'https://api.spotify.com/v1/tracks/track1', 'external_urls' => ['spotify' => 'https://open.spotify.com/track/track1']],
        ]);

        $dto = new PlaylistSyncDTO(
            playlistId: $playlist->id,
            spotifyId: $playlist->spotify_id,
            tracks: $tracks,
            metadata: []
        );

        // Should NOT call transformer or playlist client
        $transformer->shouldNotReceive('tracksToSpotifyUris');
        $playlistsClient->shouldNotReceive('replaceTracks');

        // Mock database services
        $trackService->shouldReceive('createOrUpdate')
            ->once()
            ->andReturn($track1);

        $playlistService->shouldReceive('syncTracks')
            ->once();

        $orchestrator = new SyncOrchestrator(
            $trackService,
            $artistService,
            $playlistService,
            $playlistsClient,
            $transformer
        );

        // Act - syncToSpotify is false by default
        $orchestrator->sync($dto, $playlist);

        // Assert - Mockery will verify expectations
        $this->assertTrue(true);
    }

    /** @test */
    public function it_has_set_sync_to_spotify_method(): void
    {
        // Arrange
        $trackService = Mockery::mock(TrackService::class);
        $artistService = Mockery::mock(ArtistService::class);
        $playlistService = Mockery::mock(PlaylistService::class);
        $playlistsClient = Mockery::mock(SpotifyPlaylistsClient::class);
        $transformer = Mockery::mock(PlaylistSyncDTOTransformer::class);

        $orchestrator = new SyncOrchestrator(
            $trackService,
            $artistService,
            $playlistService,
            $playlistsClient,
            $transformer
        );

        // Act & Assert
        $this->assertTrue(method_exists($orchestrator, 'setSyncToSpotify'));
        $result = $orchestrator->setSyncToSpotify(true);
        $this->assertInstanceOf(SyncOrchestrator::class, $result);
    }
}
