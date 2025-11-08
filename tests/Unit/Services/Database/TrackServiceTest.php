<?php

namespace Tests\Unit\Services\Database;

use App\Models\Artist;
use App\Models\Track;
use App\Services\Database\TrackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TrackService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrackService;
    }

    public function test_create_or_update_creates_new_track(): void
    {
        $data = [
            'id' => 'spotify_track_123',
            'name' => 'Test Track',
            'duration_ms' => 180000,
            'explicit' => false,
            'disc_number' => 1,
            'track_number' => 1,
            'popularity' => 75,
            'preview_url' => 'https://example.com/preview.mp3',
            'uri' => 'spotify:track:123',
            'href' => 'https://api.spotify.com/v1/tracks/123',
            'external_urls' => ['spotify' => 'https://open.spotify.com/track/123'],
            'is_local' => false,
        ];

        $track = $this->service->createOrUpdate($data);

        $this->assertInstanceOf(Track::class, $track);
        $this->assertEquals('spotify_track_123', $track->spotify_id);
        $this->assertEquals('Test Track', $track->name);
        $this->assertEquals(180000, $track->duration_ms);
        $this->assertDatabaseHas('tracks', [
            'spotify_id' => 'spotify_track_123',
            'name' => 'Test Track',
        ]);
    }

    public function test_create_or_update_updates_existing_track(): void
    {
        $track = Track::factory()->create([
            'spotify_id' => 'spotify_track_123',
            'name' => 'Old Name',
        ]);

        $data = [
            'id' => 'spotify_track_123',
            'name' => 'Updated Name',
            'duration_ms' => 200000,
            'uri' => 'spotify:track:123',
            'href' => 'https://api.spotify.com/v1/tracks/123',
            'external_urls' => ['spotify' => 'https://open.spotify.com/track/123'],
        ];

        $updatedTrack = $this->service->createOrUpdate($data);

        $this->assertEquals($track->id, $updatedTrack->id);
        $this->assertEquals('Updated Name', $updatedTrack->name);
        $this->assertEquals(200000, $updatedTrack->duration_ms);
        $this->assertEquals(1, Track::count());
    }

    public function test_create_or_update_handles_optional_fields(): void
    {
        $data = [
            'id' => 'spotify_track_123',
            'name' => 'Test Track',
            'duration_ms' => 180000,
            'uri' => 'spotify:track:123',
            'href' => 'https://api.spotify.com/v1/tracks/123',
            'external_urls' => ['spotify' => 'https://open.spotify.com/track/123'],
        ];

        $track = $this->service->createOrUpdate($data);

        $this->assertFalse($track->explicit);
        $this->assertEquals(1, $track->disc_number);
        $this->assertNull($track->popularity);
        $this->assertFalse($track->is_local);
    }

    public function test_sync_artists_syncs_relationship(): void
    {
        $track = Track::factory()->create();
        $artists = Artist::factory()->count(3)->create();
        $artistIds = $artists->pluck('id')->toArray();

        $this->service->syncArtists($track, $artistIds);

        $this->assertEquals(3, $track->artists()->count());
    }

    public function test_sync_artists_replaces_existing_relationships(): void
    {
        $track = Track::factory()->create();
        $oldArtists = Artist::factory()->count(3)->create();
        $track->artists()->attach($oldArtists->pluck('id'));

        $newArtists = Artist::factory()->count(2)->create();
        $this->service->syncArtists($track, $newArtists->pluck('id')->toArray());

        $this->assertEquals(2, $track->artists()->count());
        $this->assertTrue($track->artists()->where('id', $newArtists->first()->id)->exists());
        $this->assertFalse($track->artists()->where('id', $oldArtists->first()->id)->exists());
    }

    public function test_sync_to_playlist_returns_position_array(): void
    {
        $track = Track::factory()->create();

        $result = $this->service->syncToPlaylist($track, 1, 5);

        $this->assertIsArray($result);
        $this->assertArrayHasKey($track->id, $result);
        $this->assertEquals(['position' => 5], $result[$track->id]);
    }

    public function test_create_or_update_handles_missing_external_urls(): void
    {
        $data = [
            'id' => 'spotify_track_123',
            'name' => 'Test Track',
            'duration_ms' => 180000,
            'uri' => 'spotify:track:123',
            'href' => 'https://api.spotify.com/v1/tracks/123',
        ];

        $track = $this->service->createOrUpdate($data);

        $this->assertNull($track->external_url);
    }
}
