<?php

namespace App\Pipelines;

use App\DTOs\PlaylistSyncDTO;
use Closure;

class NormalizePlaylistTrackDataHandler
{
    /**
     * Handle the pipeline to normalize track data in playlist DTO.
     * Uses Collection map() for functional transformation.
     *
     * @return mixed
     */
    public function handle(PlaylistSyncDTO $dto, Closure $next)
    {
        $tracks = $dto->tracks();

        // Normalize track data using Collection
        $normalizedTracks = $tracks->map(function ($track) {
            return array_merge($track, [
                'name' => trim($track['name'] ?? ''),
                'explicit' => (bool) ($track['explicit'] ?? false),
                'is_local' => (bool) ($track['is_local'] ?? false),
                'duration_ms' => (int) ($track['duration_ms'] ?? 0),
                'popularity' => (int) ($track['popularity'] ?? 0),
            ]);
        });

        return $next($dto->withTracks($normalizedTracks));
    }
}
