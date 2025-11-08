<?php

namespace App\DTOs;

use App\Contracts\SyncDTOInterface;
use Illuminate\Support\Collection;

class PlaylistSyncDTO implements SyncDTOInterface
{
    public function __construct(
        private int $playlistId,
        private string $spotifyId,
        private Collection $tracks,
        private array $metadata = [],
    ) {}

    public function entityId(): int
    {
        return $this->playlistId;
    }

    public function playlistId(): int
    {
        return $this->playlistId;
    }

    public function setPlaylistId(int $playlistId): self
    {
        $this->playlistId = $playlistId;

        return $this;
    }

    public function spotifyId(): string
    {
        return $this->spotifyId;
    }

    public function setSpotifyId(string $spotifyId): self
    {
        $this->spotifyId = $spotifyId;

        return $this;
    }

    public function data(): Collection
    {
        return $this->tracks;
    }

    public function tracks(): Collection
    {
        return $this->tracks;
    }

    public function setTracks(Collection $tracks): self
    {
        $this->tracks = $tracks;

        return $this;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function withData(Collection $data): self
    {
        $clone = clone $this;
        $clone->tracks = $data;

        return $clone;
    }

    public function withTracks(Collection $tracks): self
    {
        $clone = clone $this;
        $clone->tracks = $tracks;

        return $clone;
    }

    /**
     * For backwards compatibility - get tracks as array.
     */
    public function getTracks(): array
    {
        return $this->tracks->all();
    }
}
