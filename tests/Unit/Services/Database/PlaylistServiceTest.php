<?php

namespace Tests\Unit\Services\Database;

use App\Models\Playlist;
use App\Models\Track;
use App\Services\Database\PlaylistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaylistServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PlaylistService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PlaylistService;
    }

    public function test_create_or_update_creates_new_playlist(): void
    {
        $data = [
            'id' => 'spotify_playlist_123',
            'name' => 'Test Playlist',
            'description' => 'A test playlist',
            'public' => true,
            'collaborative' => false,
            'tracks' => ['total' => 25],
            'uri' => 'spotify:playlist:123',
            'href' => 'https://api.spotify.com/v1/playlists/123',
            'external_urls' => ['spotify' => 'https://open.spotify.com/playlist/123'],
            'owner' => [
                'id' => 'user123',
                'display_name' => 'Test User',
            ],
        ];

        $playlist = $this->service->createOrUpdate($data);

        $this->assertInstanceOf(Playlist::class, $playlist);
        $this->assertEquals('spotify_playlist_123', $playlist->spotify_id);
        $this->assertEquals('Test Playlist', $playlist->name);
        $this->assertEquals('A test playlist', $playlist->description);
        $this->assertTrue($playlist->public);
        $this->assertFalse($playlist->collaborative);
        $this->assertEquals(25, $playlist->total_tracks);
        $this->assertDatabaseHas('playlists', [
            'spotify_id' => 'spotify_playlist_123',
            'name' => 'Test Playlist',
        ]);
    }

    public function test_create_or_update_updates_existing_playlist(): void
    {
        $playlist = Playlist::factory()->create([
            'spotify_id' => 'spotify_playlist_123',
            'name' => 'Old Playlist',
            'total_tracks' => 10,
        ]);

        $data = [
            'id' => 'spotify_playlist_123',
            'name' => 'Updated Playlist',
            'description' => 'Updated description',
            'public' => false,
            'collaborative' => true,
            'tracks' => ['total' => 50],
            'uri' => 'spotify:playlist:123',
            'href' => 'https://api.spotify.com/v1/playlists/123',
            'external_urls' => ['spotify' => 'https://open.spotify.com/playlist/123'],
            'owner' => [
                'id' => 'user456',
                'display_name' => 'Updated User',
            ],
        ];

        $updatedPlaylist = $this->service->createOrUpdate($data);

        $this->assertEquals($playlist->id, $updatedPlaylist->id);
        $this->assertEquals('Updated Playlist', $updatedPlaylist->name);
        $this->assertEquals('Updated description', $updatedPlaylist->description);
        $this->assertFalse($updatedPlaylist->public);
        $this->assertTrue($updatedPlaylist->collaborative);
        $this->assertEquals(50, $updatedPlaylist->total_tracks);
        $this->assertEquals(1, Playlist::count());
    }

    public function test_create_or_update_handles_optional_fields(): void
    {
        $data = [
            'id' => 'spotify_playlist_123',
            'name' => 'Test Playlist',
            'tracks' => ['total' => 0],
            'uri' => 'spotify:playlist:123',
            'href' => 'https://api.spotify.com/v1/playlists/123',
            'external_urls' => ['spotify' => 'https://open.spotify.com/playlist/123'],
        ];

        $playlist = $this->service->createOrUpdate($data);

        $this->assertNull($playlist->description);
        $this->assertTrue($playlist->public); // Default value
        $this->assertFalse($playlist->collaborative); // Default value
        $this->assertNull($playlist->owner_id);
        $this->assertNull($playlist->owner_name);
    }

    public function test_sync_tracks_syncs_relationship(): void
    {
        $playlist = Playlist::factory()->create();
        $tracks = Track::factory()->count(20)->create();
        $trackIds = [];

        foreach ($tracks as $index => $track) {
            $trackIds[$track->id] = ['position' => $index];
        }

        $this->service->syncTracks($playlist, $trackIds);

        $this->assertEquals(20, $playlist->tracks()->count());
        $firstTrack = $playlist->tracks()->where('track_id', $tracks->first()->id)->first();
        $this->assertEquals(0, $firstTrack->pivot->position);
    }

    public function test_sync_tracks_replaces_existing_relationships(): void
    {
        $playlist = Playlist::factory()->create();
        $oldTracks = Track::factory()->count(10)->create();
        $oldTrackIds = [];

        foreach ($oldTracks as $index => $track) {
            $oldTrackIds[$track->id] = ['position' => $index];
        }
        $playlist->tracks()->sync($oldTrackIds);

        $newTracks = Track::factory()->count(5)->create();
        $newTrackIds = [];
        foreach ($newTracks as $index => $track) {
            $newTrackIds[$track->id] = ['position' => $index];
        }

        $this->service->syncTracks($playlist, $newTrackIds);

        $this->assertEquals(5, $playlist->tracks()->count());
        $this->assertTrue($playlist->tracks()->where('track_id', $newTracks->first()->id)->exists());
        $this->assertFalse($playlist->tracks()->where('track_id', $oldTracks->first()->id)->exists());
    }

    public function test_sync_tracks_maintains_position_order(): void
    {
        $playlist = Playlist::factory()->create();
        $tracks = Track::factory()->count(5)->create();
        $trackIds = [];

        $positions = [4, 2, 0, 3, 1];
        foreach ($tracks as $index => $track) {
            $trackIds[$track->id] = ['position' => $positions[$index]];
        }

        $this->service->syncTracks($playlist, $trackIds);

        $orderedTracks = $playlist->tracks()->orderBy('position')->get();
        $this->assertEquals(0, $orderedTracks->first()->pivot->position);
        $this->assertEquals(4, $orderedTracks->last()->pivot->position);
    }

    public function test_handles_collaborative_playlists(): void
    {
        $data = [
            'id' => 'spotify_playlist_collab',
            'name' => 'Collaborative Playlist',
            'collaborative' => true,
            'tracks' => ['total' => 100],
            'uri' => 'spotify:playlist:collab',
            'href' => 'https://api.spotify.com/v1/playlists/collab',
            'external_urls' => ['spotify' => 'https://open.spotify.com/playlist/collab'],
        ];

        $playlist = $this->service->createOrUpdate($data);

        $this->assertTrue($playlist->collaborative);
    }
}
