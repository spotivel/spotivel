<?php

namespace App\Services\Database;

use App\Models\Playlist;

class PlaylistService
{
    /**
     * Create or update a playlist in the database.
     */
    public function createOrUpdate(array $data): Playlist
    {
        return Playlist::updateOrCreate(
            ['spotify_id' => $data['id']],
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'public' => $data['public'] ?? true,
                'collaborative' => $data['collaborative'] ?? false,
                'total_tracks' => $data['tracks']['total'] ?? 0,
                'uri' => $data['uri'],
                'href' => $data['href'],
                'external_url' => $data['external_urls']['spotify'] ?? null,
                'owner_id' => $data['owner']['id'] ?? null,
                'owner_name' => $data['owner']['display_name'] ?? null,
            ]
        );
    }

    /**
     * Sync tracks to a playlist.
     */
    public function syncTracks(Playlist $playlist, array $trackIds): void
    {
        $playlist->tracks()->sync($trackIds);
    }
}
