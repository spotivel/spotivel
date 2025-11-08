<?php

namespace App\Jobs;

use App\DTOs\PlaylistSyncDTO;
use App\Models\Playlist;
use App\Services\Database\ArtistService;
use App\Services\Database\PlaylistService;
use App\Services\Database\TrackService;
use App\Services\SpotifyClient;
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
        SpotifyClient $spotifyClient,
        TrackService $trackService,
        ArtistService $artistService,
        PlaylistService $playlistService
    ): void {
        $playlist = Playlist::findOrFail($this->playlistId);

        Log::info("Syncing playlist: {$playlist->name} ({$playlist->spotify_id})");

        try {
            // Fetch playlist tracks from Spotify
            $tracks = $this->fetchPlaylistTracks($spotifyClient, $playlist->spotify_id);

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
     * Fetch all tracks from a Spotify playlist.
     */
    private function fetchPlaylistTracks(SpotifyClient $spotifyClient, string $playlistId): array
    {
        $tracks = [];
        $offset = 0;
        $limit = 100;
        $hasMore = true;

        while ($hasMore) {
            $response = $spotifyClient->request()->get("/playlists/{$playlistId}/tracks", [
                'limit' => $limit,
                'offset' => $offset,
                'fields' => 'items(track(id,name,duration_ms,explicit,popularity,uri,href,external_urls,artists,album,is_local)),next',
            ])->json();

            if (isset($response['items'])) {
                foreach ($response['items'] as $item) {
                    if (isset($item['track']) && ! empty($item['track']['id'])) {
                        $tracks[] = $item['track'];
                    }
                }
            }

            $offset += $limit;

            if (! isset($response['next']) || $response['next'] === null) {
                $hasMore = false;
            }
        }

        return $tracks;
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
