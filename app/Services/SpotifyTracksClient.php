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

    /**
     * Search for tracks on Spotify.
     */
    public function search(string $query, int $limit = 20, int $offset = 0): array
    {
        $response = $this->request()->get('/search', [
            'q' => $query,
            'type' => 'track',
            'limit' => $limit,
            'offset' => $offset,
        ])->json();

        return $response['tracks']['items'] ?? [];
    }

    /**
     * Search for live versions of a track.
     * Searches using artist and track name to find live recordings.
     */
    public function searchLiveVersions(string $trackName, string $artistName, int $limit = 2): array
    {
        // Construct search query for live versions
        $query = $this->buildLiveSearchQuery($artistName, $trackName);

        return $this->search($query, $limit);
    }

    /**
     * Build search query for live versions.
     * Places artist first, then track, and adds "live" if not already present.
     */
    private function buildLiveSearchQuery(string $artistName, string $trackName): string
    {
        // Check if "live" is already in the track name
        $liveKeyword = stripos($trackName, 'live') === false ? ' live' : '';

        // Place artist first, then track name, then add live keyword if needed
        return sprintf('artist:"%s" track:"%s"%s', $artistName, $trackName, $liveKeyword);
    }
}
