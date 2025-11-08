<?php

namespace App\Services;

class SpotifyPlaylistsClient extends SpotifyClient
{
    /**
     * Fetch all tracks from a Spotify playlist.
     *
     * @param  string  $playlistId  The Spotify playlist ID
     * @param  int  $limit  Number of items to return per request
     * @param  int  $offset  Starting offset for pagination
     * @return array All tracks from the playlist
     */
    public function list(string $playlistId, int $limit = 100, int $offset = 0): array
    {
        $tracks = [];
        $hasMore = true;

        while ($hasMore) {
            $response = $this->request()->get("/playlists/{$playlistId}/tracks", [
                'limit' => $limit,
                'offset' => $offset,
                'fields' => 'items(track(id,name,duration_ms,explicit,popularity,uri,href,external_urls,artists,album,is_local)),next',
            ])->json();

            if (isset($response['items'])) {
                foreach ($response['items'] as $item) {
                    if (isset($item['track']) && ! empty($item['track']['id'])) {
                        $tracks[] = $item['track'];
                    }
                }
            }

            $offset += $limit;

            if (! isset($response['next']) || $response['next'] === null) {
                $hasMore = false;
            }
        }

        return $tracks;
    }

    /**
     * Get a single page of playlist tracks.
     *
     * @param  string  $playlistId  The Spotify playlist ID
     * @param  int  $limit  Number of items to return
     * @param  int  $offset  Starting offset
     * @return array Response with items and next URL
     */
    public function getPage(string $playlistId, int $limit = 100, int $offset = 0): array
    {
        return $this->request()->get("/playlists/{$playlistId}/tracks", [
            'limit' => $limit,
            'offset' => $offset,
            'fields' => 'items(track(id,name,duration_ms,explicit,popularity,uri,href,external_urls,artists,album,is_local)),next',
        ])->json();
    }
}
