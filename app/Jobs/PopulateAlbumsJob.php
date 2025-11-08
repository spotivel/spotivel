<?php

namespace App\Jobs;

use App\Models\Album;
use App\Services\SpotifyClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PopulateAlbumsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(SpotifyClient $spotifyClient): void
    {
        Log::info('Starting album population from Spotify');

        try {
            // Example: Fetch user's saved albums
            $response = $spotifyClient->request()->get('/me/albums', [
                'limit' => 50,
            ])->json();

            if (isset($response['items'])) {
                foreach ($response['items'] as $item) {
                    $albumData = $item['album'];
                    
                    Album::updateOrCreate(
                        ['spotify_id' => $albumData['id']],
                        [
                            'name' => $albumData['name'],
                            'album_type' => $albumData['album_type'] ?? null,
                            'release_date' => $albumData['release_date'] ?? null,
                            'release_date_precision' => $albumData['release_date_precision'] ?? null,
                            'total_tracks' => $albumData['total_tracks'] ?? null,
                            'available_markets' => $albumData['available_markets'] ?? null,
                            'images' => $albumData['images'] ?? null,
                            'uri' => $albumData['uri'],
                            'href' => $albumData['href'],
                            'external_url' => $albumData['external_urls']['spotify'] ?? null,
                        ]
                    );
                }
            }

            Log::info('Album population completed successfully');
        } catch (\Exception $e) {
            Log::error('Album population failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
