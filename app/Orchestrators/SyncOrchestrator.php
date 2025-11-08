<?php

namespace App\Orchestrators;

use App\Contracts\SyncDTOInterface;
use App\Models\Playlist;
use App\Models\Track;
use App\Services\Database\ArtistService;
use App\Services\Database\PlaylistService;
use App\Services\Database\TrackService;
use Illuminate\Pipeline\Pipeline;

class SyncOrchestrator
{
    /**
     * Pipeline handlers for this orchestrator.
     */
    protected array $handlers = [];

    public function __construct(
        protected TrackService $trackService,
        protected ArtistService $artistService,
        protected PlaylistService $playlistService
    ) {}

    /**
     * Set the pipeline handlers for processing.
     */
    public function setHandlers(array $handlers): self
    {
        $this->handlers = $handlers;

        return $this;
    }

    /**
     * Get the current pipeline handlers.
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Orchestrate the sync process through the pipeline.
     */
    public function sync(SyncDTOInterface $dto, object $entity): void
    {
        // Early return if no handlers configured
        if (empty($this->handlers)) {
            $this->saveToDatabase($dto, $entity);

            return;
        }

        // Run through pipeline
        $processedDTO = app(Pipeline::class)
            ->send($dto)
            ->through($this->handlers)
            ->thenReturn();

        // Save to database
        $this->saveToDatabase($processedDTO, $entity);
    }

    /**
     * Save processed data to database.
     * Handles different entity types polymorphically.
     */
    protected function saveToDatabase(SyncDTOInterface $dto, object $entity): void
    {
        // Handle Playlist sync
        if ($entity instanceof Playlist) {
            $this->syncPlaylistTracks($dto, $entity);

            return;
        }

        // Handle Track sync (can be extended for other entity types)
        if ($entity instanceof Track) {
            $this->syncTrackRelationships($dto, $entity);

            return;
        }

        // Extensible for other entity types
    }

    /**
     * Sync tracks to a playlist.
     */
    protected function syncPlaylistTracks(SyncDTOInterface $dto, Playlist $playlist): void
    {
        $trackIds = [];
        $position = 0;

        foreach ($dto->data()->all() as $trackData) {
            $track = $this->trackService->createOrUpdate($trackData);

            // Sync artists
            if (isset($trackData['artists'])) {
                $artistIds = [];
                foreach ($trackData['artists'] as $artistData) {
                    $artist = $this->artistService->createOrUpdate($artistData);
                    $artistIds[] = $artist->id;
                }
                $this->trackService->syncArtists($track, $artistIds);
            }

            // Store track with position
            $trackIds[$track->id] = ['position' => $position++];
        }

        // Sync tracks to playlist
        $this->playlistService->syncTracks($playlist, $trackIds);
    }

    /**
     * Sync relationships for a track.
     */
    protected function syncTrackRelationships(SyncDTOInterface $dto, Track $track): void
    {
        $data = $dto->data()->first();

        if (! $data) {
            return;
        }

        // Sync artists if present
        if (isset($data['artists'])) {
            $artistIds = [];
            foreach ($data['artists'] as $artistData) {
                $artist = $this->artistService->createOrUpdate($artistData);
                $artistIds[] = $artist->id;
            }
            $this->trackService->syncArtists($track, $artistIds);
        }
    }
}
