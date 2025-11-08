<?php

namespace App\Jobs;

use App\Services\Database\ArtistService;
use App\Services\Database\TrackService;
use App\Services\SpotifyTracksClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PopulateTracksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(
        SpotifyTracksClient $spotifyClient,
        TrackService $trackService,
        ArtistService $artistService
    ): void {
        Log::info('Starting track population from Spotify');

        try {
            // Fetch tracks from Spotify (example: user's saved tracks)
            $offset = 0;
            $limit = 50;
            $hasMore = true;

            while ($hasMore) {
                $response = $spotifyClient->getSavedTracks($limit, $offset);

                if (! isset($response['items']) || empty($response['items'])) {
                    $hasMore = false;
                    break;
                }

                $tracks = collect($response['items'])->map(fn ($item) => $item['track']);

                // Run through deduplication pipeline
                $deduplicatedTracks = app(Pipeline::class)
                    ->send($tracks)
                    ->through([
                        \App\Pipelines\RemoveDuplicateTracksHandler::class,
                        \App\Pipelines\NormalizeTrackDataHandler::class,
                    ])
                    ->thenReturn();

                // Save tracks to database
                foreach ($deduplicatedTracks as $trackData) {
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
                }

                $offset += $limit;

                if (! isset($response['next']) || $response['next'] === null) {
                    $hasMore = false;
                }
            }

            Log::info('Track population completed successfully');
        } catch (\Exception $e) {
            Log::error('Track population failed: '.$e->getMessage());
            throw $e;
        }
    }
}
