<?php

namespace App\Jobs;

use App\Services\Database\AlbumService;
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
    public function handle(SpotifyClient $spotifyClient, AlbumService $albumService): void
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

                    $albumService->createOrUpdate($albumData);
                }
            }

            Log::info('Album population completed successfully');
        } catch (\Exception $e) {
            Log::error('Album population failed: '.$e->getMessage());
            throw $e;
        }
    }
}
