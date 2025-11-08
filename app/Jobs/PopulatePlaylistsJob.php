<?php

namespace App\Jobs;

use App\Models\Playlist;
use App\Services\SpotifyClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PopulatePlaylistsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(SpotifyClient $spotifyClient): void
    {
        Log::info('Starting playlist population from Spotify');

        try {
            // Fetch user's playlists
            $offset = 0;
            $limit = 50;
            $hasMore = true;

            while ($hasMore) {
                $response = $spotifyClient->request()->get('/me/playlists', [
                    'limit' => $limit,
                    'offset' => $offset,
                ])->json();

                if (!isset($response['items']) || empty($response['items'])) {
                    $hasMore = false;
                    break;
                }

                foreach ($response['items'] as $playlistData) {
                    $playlist = Playlist::updateOrCreate(
                        ['spotify_id' => $playlistData['id']],
                        [
                            'name' => $playlistData['name'],
                            'description' => $playlistData['description'] ?? null,
                            'public' => $playlistData['public'] ?? true,
                            'collaborative' => $playlistData['collaborative'] ?? false,
                            'total_tracks' => $playlistData['tracks']['total'] ?? 0,
                            'images' => $playlistData['images'] ?? null,
                            'uri' => $playlistData['uri'],
                            'href' => $playlistData['href'],
                            'external_url' => $playlistData['external_urls']['spotify'] ?? null,
                            'owner_id' => $playlistData['owner']['id'] ?? null,
                            'owner_name' => $playlistData['owner']['display_name'] ?? null,
                        ]
                    );

                    // Queue sync job for this playlist
                    SyncPlaylistJob::dispatch($playlist->id);
                }

                $offset += $limit;

                if (!isset($response['next']) || $response['next'] === null) {
                    $hasMore = false;
                }
            }

            Log::info('Playlist population completed successfully');
        } catch (\Exception $e) {
            Log::error('Playlist population failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
