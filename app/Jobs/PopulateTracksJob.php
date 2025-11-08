<?php

namespace App\Jobs;

use App\Models\Track;
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
    public function handle(SpotifyTracksClient $spotifyClient): void
    {
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
                    $track = Track::updateOrCreate(
                        ['spotify_id' => $trackData['id']],
                        [
                            'name' => $trackData['name'],
                            'duration_ms' => $trackData['duration_ms'],
                            'explicit' => $trackData['explicit'] ?? false,
                            'disc_number' => $trackData['disc_number'] ?? 1,
                            'track_number' => $trackData['track_number'] ?? null,
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
