<?php

namespace App\Services;

class SpotifyTracksClient extends SpotifyClient
{
    /**
     * Get a track by ID.
     */
    public function getTrack(string $trackId): mixed
    {
        return $this->get("/tracks/{$trackId}");
    }

    /**
     * Get multiple tracks by IDs.
     */
    public function getTracks(array $trackIds): mixed
    {
        return $this->get('/tracks', [
            'ids' => implode(',', $trackIds),
        ]);
    }

    /**
     * Get tracks for a specific album.
     */
    public function getAlbumTracks(string $albumId, int $limit = 20, int $offset = 0): mixed
    {
        return $this->get("/albums/{$albumId}/tracks", [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get a user's saved tracks.
     */
    public function getSavedTracks(int $limit = 20, int $offset = 0): mixed
    {
        return $this->get('/me/tracks', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Save tracks for the current user.
     */
    public function saveTracks(array $trackIds): mixed
    {
        return $this->put('/me/tracks', [
            'ids' => $trackIds,
        ]);
    }

    /**
     * Remove tracks from the current user's saved tracks.
     */
    public function removeSavedTracks(array $trackIds): mixed
    {
        return $this->delete('/me/tracks?' . http_build_query(['ids' => implode(',', $trackIds)]));
    }

    /**
     * Check if tracks are saved for the current user.
     */
    public function checkSavedTracks(array $trackIds): mixed
    {
        return $this->get('/me/tracks/contains', [
            'ids' => implode(',', $trackIds),
        ]);
    }

    /**
     * Get audio features for a track.
     */
    public function getTrackAudioFeatures(string $trackId): mixed
    {
        return $this->get("/audio-features/{$trackId}");
    }

    /**
     * Get audio features for multiple tracks.
     */
    public function getTracksAudioFeatures(array $trackIds): mixed
    {
        return $this->get('/audio-features', [
            'ids' => implode(',', $trackIds),
        ]);
    }

    /**
     * Get audio analysis for a track.
     */
    public function getTrackAudioAnalysis(string $trackId): mixed
    {
        return $this->get("/audio-analysis/{$trackId}");
    }

    /**
     * Get recommendations based on seed tracks.
     */
    public function getRecommendations(array $seedTracks, int $limit = 20): mixed
    {
        return $this->get('/recommendations', [
            'seed_tracks' => implode(',', $seedTracks),
            'limit' => $limit,
        ]);
    }
}
