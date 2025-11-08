<?php

namespace App\Transformers;

use App\DTOs\PlaylistSyncDTO;
use App\Models\Playlist;

class PlaylistSyncDTOTransformer
{
    /**
     * Transform playlist data and tracks into a PlaylistSyncDTO.
     */
    public function transform(Playlist $playlist, array $tracksData): PlaylistSyncDTO
    {
        $tracks = collect($tracksData);

        return new PlaylistSyncDTO(
            playlistId: $playlist->id,
            spotifyId: $playlist->spotify_id,
            tracks: $tracks,
            metadata: [
                'name' => $playlist->name,
                'description' => $playlist->description,
                'public' => $playlist->public,
                'collaborative' => $playlist->collaborative,
                'total_tracks' => $playlist->total_tracks,
            ]
        );
    }

    /**
     * Transform raw data into a PlaylistSyncDTO.
     */
    public function transformFromArray(array $data): PlaylistSyncDTO
    {
        return new PlaylistSyncDTO(
            playlistId: $data['playlist_id'] ?? 0,
            spotifyId: $data['spotify_id'] ?? '',
            tracks: collect($data['tracks'] ?? []),
            metadata: $data['metadata'] ?? []
        );
    }

    /**
     * Transform with Spotify API response.
     */
    public function transformFromSpotifyResponse(int $playlistId, string $spotifyId, array $spotifyTracks): PlaylistSyncDTO
    {
        $tracks = collect($spotifyTracks);

        return new PlaylistSyncDTO(
            playlistId: $playlistId,
            spotifyId: $spotifyId,
            tracks: $tracks,
            metadata: []
        );
    }
}
