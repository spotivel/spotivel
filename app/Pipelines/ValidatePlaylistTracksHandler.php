<?php

namespace App\Pipelines;

use App\DTOs\PlaylistSyncDTO;
use Closure;

class ValidatePlaylistTracksHandler
{
    /**
     * Handle the pipeline to validate tracks in playlist DTO.
     * Uses Collection filter() for functional filtering.
     *
     * @return mixed
     */
    public function handle(PlaylistSyncDTO $dto, Closure $next)
    {
        $tracks = $dto->tracks();

        // Filter out invalid tracks using Collection
        $validTracks = $tracks->filter(function ($track) {
            return ! empty($track['id'])
                && ! empty($track['name'])
                && isset($track['duration_ms'])
                && $track['duration_ms'] > 0;
        })->values(); // Re-index the collection

        return $next($dto->withTracks($validTracks));
    }
}
