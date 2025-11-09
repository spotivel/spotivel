<?php

namespace Tests\Unit\Services;

use App\Contracts\HttpClientInterface;
use App\Services\ExternalClient;
use App\Services\SpotifyClient;
use App\Services\SpotifyTracksClient;
use Mockery;
use PHPUnit\Framework\TestCase;

class SpotifyTracksClientTest extends TestCase
{
    protected HttpClientInterface $mockClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClient = new ExternalClient(
            baseUrl: 'https://api.spotify.com/v1',
            headers: ['Authorization' => 'Bearer test-token']
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_create_spotify_tracks_client(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertInstanceOf(SpotifyTracksClient::class, $client);
    }

    public function test_extends_spotify_client(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        // SpotifyTracksClient should extend SpotifyClient
        $this->assertInstanceOf(SpotifyClient::class, $client);
    }

    public function test_has_get_track_method(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertTrue(method_exists($client, 'getTrack'));
    }

    public function test_has_get_tracks_method(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertTrue(method_exists($client, 'getTracks'));
    }

    public function test_has_get_album_tracks_method(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertTrue(method_exists($client, 'getAlbumTracks'));
    }

    public function test_has_get_saved_tracks_method(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertTrue(method_exists($client, 'getSavedTracks'));
    }

    public function test_has_save_tracks_method(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertTrue(method_exists($client, 'saveTracks'));
    }

    public function test_has_remove_saved_tracks_method(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertTrue(method_exists($client, 'removeSavedTracks'));
    }

    public function test_has_check_saved_tracks_method(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertTrue(method_exists($client, 'checkSavedTracks'));
    }

    public function test_has_get_track_audio_features_method(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertTrue(method_exists($client, 'getTrackAudioFeatures'));
    }

    public function test_has_get_tracks_audio_features_method(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertTrue(method_exists($client, 'getTracksAudioFeatures'));
    }

    public function test_has_get_track_audio_analysis_method(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertTrue(method_exists($client, 'getTrackAudioAnalysis'));
    }

    public function test_has_get_recommendations_method(): void
    {
        $client = new SpotifyTracksClient($this->mockClient);

        $this->assertTrue(method_exists($client, 'getRecommendations'));
    }
}
