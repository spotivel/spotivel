<?php

namespace App\Services\Database;

use App\Models\Track;

class TrackService
{
    /**
     * Create or update a track in the database.
     */
    public function createOrUpdate(array $data): Track
    {
        return Track::updateOrCreate(
            ['spotify_id' => $data['id']],
            [
                'name' => $data['name'],
                'duration_ms' => $data['duration_ms'],
                'explicit' => $data['explicit'] ?? false,
                'disc_number' => $data['disc_number'] ?? 1,
                'track_number' => $data['track_number'] ?? null,
                'popularity' => $data['popularity'] ?? null,
                'preview_url' => $data['preview_url'] ?? null,
                'uri' => $data['uri'],
                'href' => $data['href'],
                'external_url' => $data['external_urls']['spotify'] ?? null,
                'is_local' => $data['is_local'] ?? false,
            ]
        );
    }

    /**
     * Sync artists to a track.
     */
    public function syncArtists(Track $track, array $artistIds): void
    {
        $track->artists()->sync($artistIds);
    }

    /**
     * Sync track to a playlist with position.
     */
    public function syncToPlaylist(Track $track, int $playlistId, int $position): array
    {
        return [$track->id => ['position' => $position]];
    }
}
