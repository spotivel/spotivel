<?php

namespace App\Pipelines;

use App\DTOs\PlaylistSyncDTO;
use Closure;

class RemoveDuplicatePlaylistTracksHandler
{
    /**
     * Handle the pipeline to remove duplicate tracks from playlist DTO.
     *
     * @param  PlaylistSyncDTO  $dto
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(PlaylistSyncDTO $dto, Closure $next)
    {
        $tracks = $dto->getTracks();
        
        // Remove duplicates based on Spotify ID
        $seen = [];
        $uniqueTracks = [];
        
        foreach ($tracks as $track) {
            if (!isset($seen[$track['id']])) {
                $uniqueTracks[] = $track;
                $seen[$track['id']] = true;
            }
        }

        return $next($dto->withTracks($uniqueTracks));
    }
}
