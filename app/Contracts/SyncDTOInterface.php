<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface SyncDTOInterface
{
    /**
     * Get the entity ID (track, playlist, album, etc.).
     */
    public function entityId(): int;

    /**
     * Get the Spotify ID.
     */
    public function spotifyId(): string;

    /**
     * Get the data collection.
     */
    public function data(): Collection;

    /**
     * Get metadata array.
     */
    public function metadata(): array;

    /**
     * Create a new instance with updated data (immutability pattern).
     */
    public function withData(Collection $data): self;
}
