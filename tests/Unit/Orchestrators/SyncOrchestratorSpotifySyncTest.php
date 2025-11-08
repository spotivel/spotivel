<?php

namespace Tests\Unit\Orchestrators;

use App\DTOs\PlaylistSyncDTO;
use App\Jobs\SyncPlaylistToSpotifyJob;
use App\Models\Playlist;
use App\Orchestrators\SyncOrchestrator;
use App\Services\Database\ArtistService;
use App\Services\Database\PlaylistService;
use App\Services\Database\TrackService;
use App\Transformers\PlaylistSyncDTOTransformer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SyncOrchestratorSpotifySyncTest extends TestCase
{
    use RefreshDatabase;

    protected SyncOrchestrator $orchestrator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orchestrator = new SyncOrchestrator(
            new TrackService,
            new ArtistService,
            new PlaylistService,
            new PlaylistSyncDTOTransformer
        );
    }

    /** @test */
    public function it_dispatches_sync_job_when_enabled(): void
    {
        // Arrange
        Queue::fake();

        $playlist = Playlist::factory()->create([
            'spotify_id' => 'playlist123',
            'name' => 'Test Playlist',
        ]);

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

        // Act
        $this->orchestrator->setSyncToSpotify(true);
        $this->orchestrator->sync($dto, $playlist);

        // Assert
        Queue::assertPushed(SyncPlaylistToSpotifyJob::class, function ($job) use ($playlist) {
            return $job->playlistId === $playlist->id &&
                   is_array($job->trackUris) &&
                   count($job->trackUris) === 2;
        });
    }

    /** @test */
    public function it_does_not_dispatch_sync_job_when_disabled(): void
    {
        // Arrange
        Queue::fake();

        $playlist = Playlist::factory()->create([
            'spotify_id' => 'playlist123',
            'name' => 'Test Playlist',
        ]);

        $tracks = collect([
            ['id' => 'track1', 'name' => 'Track 1', 'uri' => 'spotify:track:track1', 'duration_ms' => 180000, 'explicit' => false, 'href' => 'https://api.spotify.com/v1/tracks/track1', 'external_urls' => ['spotify' => 'https://open.spotify.com/track/track1']],
        ]);

        $dto = new PlaylistSyncDTO(
            playlistId: $playlist->id,
            spotifyId: $playlist->spotify_id,
            tracks: $tracks,
            metadata: []
        );

        // Act - syncToSpotify is false by default
        $this->orchestrator->sync($dto, $playlist);

        // Assert
        Queue::assertNothingPushed();
    }

    /** @test */
    public function it_has_set_sync_to_spotify_method(): void
    {
        // Act & Assert
        $this->assertTrue(method_exists($this->orchestrator, 'setSyncToSpotify'));
        $result = $this->orchestrator->setSyncToSpotify(true);
        $this->assertInstanceOf(SyncOrchestrator::class, $result);
    }

    /** @test */
    public function it_saves_tracks_to_database_before_dispatching_sync_job(): void
    {
        // Arrange
        Queue::fake();

        $playlist = Playlist::factory()->create([
            'spotify_id' => 'playlist123',
            'name' => 'Test Playlist',
        ]);

        $tracks = collect([
            ['id' => 'track1', 'name' => 'Track 1', 'uri' => 'spotify:track:track1', 'duration_ms' => 180000, 'explicit' => false, 'href' => 'https://api.spotify.com/v1/tracks/track1', 'external_urls' => ['spotify' => 'https://open.spotify.com/track/track1']],
        ]);

        $dto = new PlaylistSyncDTO(
            playlistId: $playlist->id,
            spotifyId: $playlist->spotify_id,
            tracks: $tracks,
            metadata: []
        );

        // Act
        $this->orchestrator->setSyncToSpotify(true);
        $this->orchestrator->sync($dto, $playlist);

        // Assert - Track should be saved to database
        $this->assertDatabaseHas('tracks', [
            'spotify_id' => 'track1',
            'name' => 'Track 1',
        ]);

        // And job should be dispatched
        Queue::assertPushed(SyncPlaylistToSpotifyJob::class);
    }
}
