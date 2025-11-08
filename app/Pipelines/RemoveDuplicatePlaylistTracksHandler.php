<?php

namespace App\Pipelines;

use App\DTOs\PlaylistSyncDTO;
use Closure;

class RemoveDuplicatePlaylistTracksHandler
{
    /**
     * Handle the pipeline to remove duplicate tracks from playlist DTO.
     * Uses Collection unique() with closure for spotify_id|duration_ms|popularity.
     *
     * @param  PlaylistSyncDTO  $dto
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(PlaylistSyncDTO $dto, Closure $next)
    {
        $tracks = $dto->tracks();
        
        // Remove duplicates using unique() with closure
        $uniqueTracks = $tracks->unique(function ($track) {
            return ($track['id'] ?? '') . '|' . 
                   ($track['duration_ms'] ?? '') . '|' . 
                   ($track['popularity'] ?? '');
        });

        return $next($dto->withTracks($uniqueTracks->values()));
    }
}
