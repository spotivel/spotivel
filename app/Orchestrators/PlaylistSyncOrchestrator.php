<?php

namespace App\Orchestrators;

use App\DTOs\PlaylistSyncDTO;
use App\Models\Playlist;
use App\Services\Database\ArtistService;
use App\Services\Database\PlaylistService;
use App\Services\Database\TrackService;
use Illuminate\Pipeline\Pipeline;

class PlaylistSyncOrchestrator
{
    public function __construct(
        private TrackService $trackService,
        private ArtistService $artistService,
        private PlaylistService $playlistService
    ) {}

    /**
     * Orchestrate the playlist sync process through the pipeline.
     */
    public function sync(PlaylistSyncDTO $dto, Playlist $playlist): void
    {
        // Run through pipeline
        $processedDTO = app(Pipeline::class)
            ->send($dto)
            ->through([
                \App\Pipelines\RemoveDuplicatePlaylistTracksHandler::class,
                \App\Pipelines\NormalizePlaylistTrackDataHandler::class,
                \App\Pipelines\ValidatePlaylistTracksHandler::class,
            ])
            ->thenReturn();

        // Save to database
        $this->saveToDatabase($processedDTO, $playlist);
    }

    /**
     * Save processed tracks to database.
     */
    private function saveToDatabase(PlaylistSyncDTO $dto, Playlist $playlist): void
    {
        $trackIds = [];
        $position = 0;

        foreach ($dto->getTracks() as $trackData) {
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
}
