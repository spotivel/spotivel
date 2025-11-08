<?php

namespace App\Pipelines;

use App\DTOs\PlaylistSyncDTO;
use Closure;

class ValidatePlaylistTracksHandler
{
    /**
     * Handle the pipeline to validate tracks in playlist DTO.
     *
     * @param  PlaylistSyncDTO  $dto
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(PlaylistSyncDTO $dto, Closure $next)
    {
        $tracks = $dto->getTracks();
        
        // Filter out invalid tracks (missing required fields)
        $validTracks = array_filter($tracks, function ($track) {
            return !empty($track['id']) 
                && !empty($track['name']) 
                && isset($track['duration_ms'])
                && $track['duration_ms'] > 0;
        });

        // Re-index array
        $validTracks = array_values($validTracks);

        return $next($dto->withTracks($validTracks));
    }
}
