<?php

namespace App\Jobs;

use App\DTOs\PlaylistSyncDTO;
use App\Models\Playlist;
use App\Services\Database\ArtistService;
use App\Services\Database\PlaylistService;
use App\Services\Database\TrackService;
use App\Services\SpotifyPlaylistsClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Pipeline\Pipeline;
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
        TrackService $trackService,
        ArtistService $artistService,
        PlaylistService $playlistService
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
                tracks: $tracks,
                metadata: [
                    'name' => $playlist->name,
                    'total_tracks' => count($tracks),
                ]
            );

            // Run through pipeline
            $processedDTO = app(Pipeline::class)
                ->send($dto)
                ->through([
                    \App\Pipelines\RemoveDuplicatePlaylistTracksHandler::class,
                    \App\Pipelines\NormalizePlaylistTrackDataHandler::class,
                    \App\Pipelines\ValidatePlaylistTracksHandler::class,
                ])
                ->thenReturn();

            // Sync tracks to database and attach to playlist
            $this->syncTracksToDatabase($processedDTO, $playlist, $trackService, $artistService, $playlistService);

            Log::info("Playlist sync completed: {$playlist->name}");
        } catch (\Exception $e) {
            Log::error("Playlist sync failed for {$playlist->name}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync tracks to database and attach to playlist.
     */
    private function syncTracksToDatabase(
        PlaylistSyncDTO $dto,
        Playlist $playlist,
        TrackService $trackService,
        ArtistService $artistService,
        PlaylistService $playlistService
    ): void {
        $trackIds = [];
        $position = 0;

        foreach ($dto->getTracks() as $trackData) {
            $track = $trackService->createOrUpdate($trackData);

            // Sync artists
            if (isset($trackData['artists'])) {
                $artistIds = [];
                foreach ($trackData['artists'] as $artistData) {
                    $artist = $artistService->createOrUpdate($artistData);
                    $artistIds[] = $artist->id;
                }
                $trackService->syncArtists($track, $artistIds);
            }

            // Store track with position
            $trackIds[$track->id] = ['position' => $position++];
        }

        // Sync tracks to playlist
        $playlistService->syncTracks($playlist, $trackIds);
    }
}
