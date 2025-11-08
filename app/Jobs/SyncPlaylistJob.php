<?php

namespace App\Jobs;

use App\DTOs\PlaylistSyncDTO;
use App\Models\Playlist;
use App\Models\Track;
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
    public function handle(SpotifyClient $spotifyClient): void
    {
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
            $this->syncTracksToDatabase($processedDTO, $playlist);

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
    private function syncTracksToDatabase(PlaylistSyncDTO $dto, Playlist $playlist): void
    {
        $trackIds = [];
        $position = 0;

        foreach ($dto->getTracks() as $trackData) {
            $track = Track::updateOrCreate(
                ['spotify_id' => $trackData['id']],
                [
                    'name' => $trackData['name'],
                    'duration_ms' => $trackData['duration_ms'],
                    'explicit' => $trackData['explicit'] ?? false,
                    'popularity' => $trackData['popularity'] ?? null,
                    'preview_url' => $trackData['preview_url'] ?? null,
                    'uri' => $trackData['uri'],
                    'href' => $trackData['href'],
                    'external_url' => $trackData['external_urls']['spotify'] ?? null,
                    'is_local' => $trackData['is_local'] ?? false,
                ]
            );

            // Sync artists
            if (isset($trackData['artists'])) {
                $artistIds = [];
                foreach ($trackData['artists'] as $artistData) {
                    $artist = \App\Models\Artist::updateOrCreate(
                        ['spotify_id' => $artistData['id']],
                        [
                            'name' => $artistData['name'],
                            'uri' => $artistData['uri'] ?? null,
                            'href' => $artistData['href'] ?? null,
                            'external_url' => $artistData['external_urls']['spotify'] ?? null,
                        ]
                    );
                    $artistIds[] = $artist->id;
                }
                $track->artists()->sync($artistIds);
            }

            // Store track with position
            $trackIds[$track->id] = ['position' => $position++];
        }

        // Sync tracks to playlist
        $playlist->tracks()->sync($trackIds);
    }
}
