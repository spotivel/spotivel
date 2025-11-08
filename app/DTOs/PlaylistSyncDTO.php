<?php

namespace App\DTOs;

class PlaylistSyncDTO
{
    public function __construct(
        public readonly int $playlistId,
        public readonly string $spotifyId,
        public readonly array $tracks,
        public readonly array $metadata = [],
    ) {
    }

    public function getPlaylistId(): int
    {
        return $this->playlistId;
    }

    public function getSpotifyId(): string
    {
        return $this->spotifyId;
    }

    public function getTracks(): array
    {
        return $this->tracks;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function withTracks(array $tracks): self
    {
        return new self(
            $this->playlistId,
            $this->spotifyId,
            $tracks,
            $this->metadata
        );
    }
}
