<?php

namespace App\Jobs;

use App\DTOs\PlaylistSyncDTO;
use App\Models\Playlist;
use App\Orchestrators\SyncOrchestrator;
use App\Services\SpotifyPlaylistsClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncPlaylistJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $playlistId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        SpotifyPlaylistsClient $playlistsClient,
        SyncOrchestrator $orchestrator
    ): void {
        $playlist = Playlist::findOrFail($this->playlistId);

        Log::info("Syncing playlist: {$playlist->name} ({$playlist->spotify_id})");

        try {
            // Fetch playlist tracks from Spotify using the specialized client
            $tracks = $playlistsClient->list($playlist->spotify_id);

            // Create DTO
            $dto = new PlaylistSyncDTO(
                playlistId: $playlist->id,
                spotifyId: $playlist->spotify_id,
                tracks: collect($tracks),
                metadata: [
                    'name' => $playlist->name,
                    'total_tracks' => count($tracks),
                ]
            );

            // Configure handlers for playlist sync pipeline
            $orchestrator->setHandlers([
                \App\Pipelines\RemoveDuplicatePlaylistTracksHandler::class,
                \App\Pipelines\NormalizePlaylistTrackDataHandler::class,
                \App\Pipelines\ValidatePlaylistTracksHandler::class,
                \App\Pipelines\AddLiveVersionsHandler::class,
            ]);

            // Enable syncing back to Spotify API
            $orchestrator->setSyncToSpotify(true);

            // Dispatch to orchestrator for processing and saving
            $orchestrator->sync($dto, $playlist);

            Log::info("Playlist sync completed: {$playlist->name}");
        } catch (\Exception $e) {
            Log::error("Playlist sync failed for {$playlist->name}: ".$e->getMessage());
            throw $e;
        }
    }
}
