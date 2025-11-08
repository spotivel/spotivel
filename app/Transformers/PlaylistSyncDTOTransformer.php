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

    /**
     * Transform DTO back to Spotify API payload format.
     * Used for sending data back to Spotify API (create/update playlist operations).
     */
    public function toSpotifyPayload(PlaylistSyncDTO $dto): array
    {
        $metadata = $dto->metadata();

        $payload = [
            'name' => $metadata['name'] ?? '',
            'description' => $metadata['description'] ?? '',
        ];

        if (array_key_exists('public', $metadata)) {
            $payload['public'] = $metadata['public'];
        }

        if (array_key_exists('collaborative', $metadata)) {
            $payload['collaborative'] = $metadata['collaborative'];
        }

        return $payload;
    }

    /**
     * Transform DTO tracks to Spotify API track URIs format.
     * Used for adding tracks to a playlist.
     */
    public function tracksToSpotifyUris(PlaylistSyncDTO $dto): array
    {
        return $dto->tracks()
            ->map(fn ($track) => $track['uri'] ?? 'spotify:track:'.$track['id'])
            ->values()
            ->all();
    }

    /**
     * Transform DTO to complete Spotify playlist update payload.
     * Includes both playlist metadata and track URIs.
     */
    public function toCompleteSpotifyPayload(PlaylistSyncDTO $dto): array
    {
        return [
            'playlist' => $this->toSpotifyPayload($dto),
            'tracks' => [
                'uris' => $this->tracksToSpotifyUris($dto),
            ],
        ];
    }
}
