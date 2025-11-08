<?php

namespace Tests\Unit\Services\Database;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;
use App\Services\Database\ArtistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtistServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ArtistService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ArtistService;
    }

    public function test_create_or_update_creates_new_artist(): void
    {
        $data = [
            'id' => 'spotify_artist_123',
            'name' => 'Test Artist',
            'popularity' => 85,
            'followers' => ['total' => 1000000],
            'uri' => 'spotify:artist:123',
            'href' => 'https://api.spotify.com/v1/artists/123',
            'external_urls' => ['spotify' => 'https://open.spotify.com/artist/123'],
        ];

        $artist = $this->service->createOrUpdate($data);

        $this->assertInstanceOf(Artist::class, $artist);
        $this->assertEquals('spotify_artist_123', $artist->spotify_id);
        $this->assertEquals('Test Artist', $artist->name);
        $this->assertEquals(85, $artist->popularity);
        $this->assertEquals(1000000, $artist->followers);
        $this->assertDatabaseHas('artists', [
            'spotify_id' => 'spotify_artist_123',
            'name' => 'Test Artist',
        ]);
    }

    public function test_create_or_update_updates_existing_artist(): void
    {
        $artist = Artist::factory()->create([
            'spotify_id' => 'spotify_artist_123',
            'name' => 'Old Name',
            'popularity' => 50,
        ]);

        $data = [
            'id' => 'spotify_artist_123',
            'name' => 'Updated Name',
            'popularity' => 90,
            'followers' => ['total' => 2000000],
            'uri' => 'spotify:artist:123',
            'href' => 'https://api.spotify.com/v1/artists/123',
            'external_urls' => ['spotify' => 'https://open.spotify.com/artist/123'],
        ];

        $updatedArtist = $this->service->createOrUpdate($data);

        $this->assertEquals($artist->id, $updatedArtist->id);
        $this->assertEquals('Updated Name', $updatedArtist->name);
        $this->assertEquals(90, $updatedArtist->popularity);
        $this->assertEquals(2000000, $updatedArtist->followers);
        $this->assertEquals(1, Artist::count());
    }

    public function test_create_or_update_handles_optional_fields(): void
    {
        $data = [
            'id' => 'spotify_artist_123',
            'name' => 'Test Artist',
        ];

        $artist = $this->service->createOrUpdate($data);

        $this->assertNull($artist->popularity);
        $this->assertNull($artist->followers);
        $this->assertNull($artist->uri);
    }

    public function test_sync_tracks_syncs_relationship(): void
    {
        $artist = Artist::factory()->create();
        $tracks = Track::factory()->count(5)->create();
        $trackIds = $tracks->pluck('id')->toArray();

        $this->service->syncTracks($artist, $trackIds);

        $this->assertEquals(5, $artist->tracks()->count());
    }

    public function test_sync_tracks_replaces_existing_relationships(): void
    {
        $artist = Artist::factory()->create();
        $oldTracks = Track::factory()->count(3)->create();
        $artist->tracks()->attach($oldTracks->pluck('id'));

        $newTracks = Track::factory()->count(2)->create();
        $this->service->syncTracks($artist, $newTracks->pluck('id')->toArray());

        $this->assertEquals(2, $artist->tracks()->count());
        $this->assertTrue($artist->tracks()->where('id', $newTracks->first()->id)->exists());
        $this->assertFalse($artist->tracks()->where('id', $oldTracks->first()->id)->exists());
    }

    public function test_sync_albums_syncs_relationship(): void
    {
        $artist = Artist::factory()->create();
        $albums = Album::factory()->count(3)->create();
        $albumIds = $albums->pluck('id')->toArray();

        $this->service->syncAlbums($artist, $albumIds);

        $this->assertEquals(3, $artist->albums()->count());
    }

    public function test_sync_albums_replaces_existing_relationships(): void
    {
        $artist = Artist::factory()->create();
        $oldAlbums = Album::factory()->count(4)->create();
        $artist->albums()->attach($oldAlbums->pluck('id'));

        $newAlbums = Album::factory()->count(2)->create();
        $this->service->syncAlbums($artist, $newAlbums->pluck('id')->toArray());

        $this->assertEquals(2, $artist->albums()->count());
        $this->assertTrue($artist->albums()->where('id', $newAlbums->first()->id)->exists());
        $this->assertFalse($artist->albums()->where('id', $oldAlbums->first()->id)->exists());
    }

    public function test_create_or_update_handles_missing_followers(): void
    {
        $data = [
            'id' => 'spotify_artist_123',
            'name' => 'Test Artist',
            'uri' => 'spotify:artist:123',
            'href' => 'https://api.spotify.com/v1/artists/123',
        ];

        $artist = $this->service->createOrUpdate($data);

        $this->assertNull($artist->followers);
    }
}
