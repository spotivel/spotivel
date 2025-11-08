<?php

namespace App\Services;

class SpotifyTracksClient extends SpotifyClient
{
    /**
     * Get a track by ID.
     */
    public function getTrack(string $trackId): mixed
    {
        return $this->request()->get("/tracks/{$trackId}")->json();
    }

    /**
     * Get multiple tracks by IDs.
     */
    public function getTracks(array $trackIds): mixed
    {
        return $this->request()->get('/tracks', [
            'ids' => implode(',', $trackIds),
        ])->json();
    }

    /**
     * Get tracks for a specific album.
     */
    public function getAlbumTracks(string $albumId, int $limit = 20, int $offset = 0): mixed
    {
        return $this->request()->get("/albums/{$albumId}/tracks", [
            'limit' => $limit,
            'offset' => $offset,
        ])->json();
    }

    /**
     * Get a user's saved tracks.
     */
    public function getSavedTracks(int $limit = 20, int $offset = 0): mixed
    {
        return $this->request()->get('/me/tracks', [
            'limit' => $limit,
            'offset' => $offset,
        ])->json();
    }

    /**
     * Save tracks for the current user.
     */
    public function saveTracks(array $trackIds): mixed
    {
        return $this->request()->put('/me/tracks', [
            'ids' => $trackIds,
        ])->json();
    }

    /**
     * Remove tracks from the current user's saved tracks.
     */
    public function removeSavedTracks(array $trackIds): mixed
    {
        return $this->request()->delete('/me/tracks?' . http_build_query(['ids' => implode(',', $trackIds)]))->json();
    }

    /**
     * Check if tracks are saved for the current user.
     */
    public function checkSavedTracks(array $trackIds): mixed
    {
        return $this->request()->get('/me/tracks/contains', [
            'ids' => implode(',', $trackIds),
        ])->json();
    }

    /**
     * Get audio features for a track.
     */
    public function getTrackAudioFeatures(string $trackId): mixed
    {
        return $this->request()->get("/audio-features/{$trackId}")->json();
    }

    /**
     * Get audio features for multiple tracks.
     */
    public function getTracksAudioFeatures(array $trackIds): mixed
    {
        return $this->request()->get('/audio-features', [
            'ids' => implode(',', $trackIds),
        ])->json();
    }

    /**
     * Get audio analysis for a track.
     */
    public function getTrackAudioAnalysis(string $trackId): mixed
    {
        return $this->request()->get("/audio-analysis/{$trackId}")->json();
    }

    /**
     * Get recommendations based on seed tracks.
     */
    public function getRecommendations(array $seedTracks, int $limit = 20): mixed
    {
        return $this->request()->get('/recommendations', [
            'seed_tracks' => implode(',', $seedTracks),
            'limit' => $limit,
        ])->json();
    }
}
