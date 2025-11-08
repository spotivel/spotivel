<?php

namespace App\Services\Database;

use App\Models\Album;

class AlbumService
{
    /**
     * Create or update an album in the database.
     */
    public function createOrUpdate(array $data): Album
    {
        return Album::updateOrCreate(
            ['spotify_id' => $data['id']],
            [
                'name' => $data['name'],
                'album_type' => $data['album_type'] ?? null,
                'release_date' => $data['release_date'] ?? null,
                'release_date_precision' => $data['release_date_precision'] ?? null,
                'total_tracks' => $data['total_tracks'] ?? null,
                'uri' => $data['uri'],
                'href' => $data['href'],
                'external_url' => $data['external_urls']['spotify'] ?? null,
            ]
        );
    }

    /**
     * Sync artists to an album.
     */
    public function syncArtists(Album $album, array $artistIds): void
    {
        $album->artists()->sync($artistIds);
    }

    /**
     * Sync tracks to an album.
     */
    public function syncTracks(Album $album, array $trackIds): void
    {
        $album->tracks()->sync($trackIds);
    }
}
