<?php

namespace App\Services;

use App\Enums\HttpMethod;

class SpotifyTracksClient extends SpotifyClient
{
    /**
     * Get a track by ID.
     */
    public function getTrack(string $trackId): mixed
    {
        return $this->request(HttpMethod::GET, "/tracks/{$trackId}");
    }

    /**
     * Get multiple tracks by IDs.
     */
    public function getTracks(array $trackIds): mixed
    {
        return $this->request(HttpMethod::GET, '/tracks', [
            'query' => ['ids' => implode(',', $trackIds)],
        ]);
    }

    /**
     * Get tracks for a specific album.
     */
    public function getAlbumTracks(string $albumId, int $limit = 20, int $offset = 0): mixed
    {
        return $this->request(HttpMethod::GET, "/albums/{$albumId}/tracks", [
            'query' => [
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);
    }

    /**
     * Get a user's saved tracks.
     */
    public function getSavedTracks(int $limit = 20, int $offset = 0): mixed
    {
        return $this->request(HttpMethod::GET, '/me/tracks', [
            'query' => [
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);
    }

    /**
     * Save tracks for the current user.
     */
    public function saveTracks(array $trackIds): mixed
    {
        return $this->request(HttpMethod::PUT, '/me/tracks', [
            'body' => ['ids' => $trackIds],
        ]);
    }

    /**
     * Remove tracks from the current user's saved tracks.
     */
    public function removeSavedTracks(array $trackIds): mixed
    {
        return $this->request(HttpMethod::DELETE, '/me/tracks', [
            'query' => ['ids' => implode(',', $trackIds)],
        ]);
    }

    /**
     * Check if tracks are saved for the current user.
     */
    public function checkSavedTracks(array $trackIds): mixed
    {
        return $this->request(HttpMethod::GET, '/me/tracks/contains', [
            'query' => ['ids' => implode(',', $trackIds)],
        ]);
    }

    /**
     * Get audio features for a track.
     */
    public function getTrackAudioFeatures(string $trackId): mixed
    {
        return $this->request(HttpMethod::GET, "/audio-features/{$trackId}");
    }

    /**
     * Get audio features for multiple tracks.
     */
    public function getTracksAudioFeatures(array $trackIds): mixed
    {
        return $this->request(HttpMethod::GET, '/audio-features', [
            'query' => ['ids' => implode(',', $trackIds)],
        ]);
    }

    /**
     * Get audio analysis for a track.
     */
    public function getTrackAudioAnalysis(string $trackId): mixed
    {
        return $this->request(HttpMethod::GET, "/audio-analysis/{$trackId}");
    }

    /**
     * Get recommendations based on seed tracks.
     */
    public function getRecommendations(array $seedTracks, int $limit = 20): mixed
    {
        return $this->request(HttpMethod::GET, '/recommendations', [
            'query' => [
                'seed_tracks' => implode(',', $seedTracks),
                'limit' => $limit,
            ],
        ]);
    }
}
