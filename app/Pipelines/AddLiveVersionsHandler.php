<?php

namespace App\Pipelines;

use App\Contracts\SyncDTOInterface;
use App\Services\SpotifyTracksClient;
use Closure;
use Illuminate\Support\Facades\Log;

class AddLiveVersionsHandler
{
    public function __construct(
        protected SpotifyTracksClient $tracksClient
    ) {}

    /**
     * Handle the pipeline to add live versions of tracks.
     * For each track in the DTO, searches for 2 live versions and adds them to the collection.
     *
     * @return mixed
     */
    public function handle(SyncDTOInterface $dto, Closure $next)
    {
        // Early return if no data
        if ($dto->data()->isEmpty()) {
            return $next($dto);
        }

        $tracks = $dto->data();
        $tracksWithLive = collect();

        foreach ($tracks as $track) {
            // Add original track
            $tracksWithLive->push($track);

            // Get track name and primary artist
            $trackName = $track['name'] ?? '';
            $artistName = $track['artists'][0]['name'] ?? '';

            // Skip if no track name or artist
            if (empty($trackName) || empty($artistName)) {
                continue;
            }

            try {
                // Search for live versions (limit 2)
                $liveVersions = $this->tracksClient->searchLiveVersions($trackName, $artistName, 2);

                // Add live versions to collection
                foreach ($liveVersions as $liveTrack) {
                    // Ensure it's not the same track we already have
                    if (isset($liveTrack['id']) && $liveTrack['id'] !== $track['id']) {
                        $tracksWithLive->push($liveTrack);
                    }
                }
            } catch (\Exception $e) {
                // Log error but continue processing
                Log::warning("Failed to search live versions for track: {$trackName}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $next($dto->withData($tracksWithLive));
    }
}
