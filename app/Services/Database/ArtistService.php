<?php

namespace App\Services\Database;

use App\Models\Artist;

class ArtistService
{
    /**
     * Create or update an artist in the database.
     */
    public function createOrUpdate(array $data): Artist
    {
        return Artist::updateOrCreate(
            ['spotify_id' => $data['id']],
            [
                'name' => $data['name'],
                'popularity' => $data['popularity'] ?? null,
                'followers' => $data['followers']['total'] ?? null,
                'uri' => $data['uri'] ?? null,
                'href' => $data['href'] ?? null,
                'external_url' => $data['external_urls']['spotify'] ?? null,
            ]
        );
    }

    /**
     * Sync tracks to an artist.
     */
    public function syncTracks(Artist $artist, array $trackIds): void
    {
        $artist->tracks()->sync($trackIds);
    }

    /**
     * Sync albums to an artist.
     */
    public function syncAlbums(Artist $artist, array $albumIds): void
    {
        $artist->albums()->sync($albumIds);
    }
}
