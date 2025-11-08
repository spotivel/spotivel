<?php

namespace App\Jobs;

use App\Services\Database\ArtistService;
use App\Services\SpotifyClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PopulateArtistsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(SpotifyClient $spotifyClient, ArtistService $artistService): void
    {
        Log::info('Starting artist population from Spotify');

        try {
            // Example: Fetch user's followed artists
            $response = $spotifyClient->request()->get('/me/following', [
                'type' => 'artist',
                'limit' => 50,
            ])->json();

            if (isset($response['artists']['items'])) {
                foreach ($response['artists']['items'] as $artistData) {
                    $artistService->createOrUpdate($artistData);
                }
            }

            Log::info('Artist population completed successfully');
        } catch (\Exception $e) {
            Log::error('Artist population failed: '.$e->getMessage());
            throw $e;
        }
    }
}
