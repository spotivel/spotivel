<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Support\Collection;

class RemoveDuplicateTracksHandler
{
    /**
     * Handle the pipeline to remove duplicate tracks.
     *
     * @param  Collection  $tracks
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($tracks, Closure $next)
    {
        // Remove duplicates based on Spotify ID
        $uniqueTracks = $tracks->unique('id');

        // Also check for duplicates by name and duration
        $uniqueTracks = $uniqueTracks->unique(function ($track) {
            return strtolower($track['name']) . '-' . ($track['duration_ms'] ?? '');
        });

        return $next($uniqueTracks);
    }
}
