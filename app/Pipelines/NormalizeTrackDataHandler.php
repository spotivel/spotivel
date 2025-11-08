<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Support\Collection;

class NormalizeTrackDataHandler
{
    /**
     * Handle the pipeline to normalize track data.
     *
     * @param  Collection  $tracks
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($tracks, Closure $next)
    {
        // Normalize track data (e.g., trim whitespace, ensure consistent format)
        $normalizedTracks = $tracks->map(function ($track) {
            return array_merge($track, [
                'name' => trim($track['name'] ?? ''),
                'explicit' => (bool) ($track['explicit'] ?? false),
                'is_local' => (bool) ($track['is_local'] ?? false),
            ]);
        });

        return $next($normalizedTracks);
    }
}
