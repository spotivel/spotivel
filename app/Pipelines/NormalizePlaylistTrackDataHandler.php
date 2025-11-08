<?php

namespace App\Pipelines;

use App\DTOs\PlaylistSyncDTO;
use Closure;

class NormalizePlaylistTrackDataHandler
{
    /**
     * Handle the pipeline to normalize track data in playlist DTO.
     *
     * @param  PlaylistSyncDTO  $dto
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(PlaylistSyncDTO $dto, Closure $next)
    {
        $tracks = $dto->getTracks();
        
        // Normalize track data
        $normalizedTracks = array_map(function ($track) {
            return array_merge($track, [
                'name' => trim($track['name'] ?? ''),
                'explicit' => (bool) ($track['explicit'] ?? false),
                'is_local' => (bool) ($track['is_local'] ?? false),
                'duration_ms' => (int) ($track['duration_ms'] ?? 0),
            ]);
        }, $tracks);

        return $next($dto->withTracks($normalizedTracks));
    }
}
