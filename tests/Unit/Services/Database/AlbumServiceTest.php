<?php

namespace Tests\Unit\Services\Database;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;
use App\Services\Database\AlbumService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlbumServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AlbumService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AlbumService;
    }

    public function test_create_or_update_creates_new_album(): void
    {
        $data = [
            'id' => 'spotify_album_123',
            'name' => 'Test Album',
            'album_type' => 'album',
            'release_date' => '2023-01-01',
            'release_date_precision' => 'day',
            'total_tracks' => 12,
            'uri' => 'spotify:album:123',
            'href' => 'https://api.spotify.com/v1/albums/123',
            'external_urls' => ['spotify' => 'https://open.spotify.com/album/123'],
        ];

        $album = $this->service->createOrUpdate($data);

        $this->assertInstanceOf(Album::class, $album);
        $this->assertEquals('spotify_album_123', $album->spotify_id);
        $this->assertEquals('Test Album', $album->name);
        $this->assertEquals('album', $album->album_type);
        $this->assertEquals('2023-01-01', $album->release_date);
        $this->assertEquals(12, $album->total_tracks);
        $this->assertDatabaseHas('albums', [
            'spotify_id' => 'spotify_album_123',
            'name' => 'Test Album',
        ]);
    }

    public function test_create_or_update_updates_existing_album(): void
    {
        $album = Album::factory()->create([
            'spotify_id' => 'spotify_album_123',
            'name' => 'Old Album',
            'total_tracks' => 10,
        ]);

        $data = [
            'id' => 'spotify_album_123',
            'name' => 'Updated Album',
            'album_type' => 'compilation',
            'total_tracks' => 15,
            'uri' => 'spotify:album:123',
            'href' => 'https://api.spotify.com/v1/albums/123',
            'external_urls' => ['spotify' => 'https://open.spotify.com/album/123'],
        ];

        $updatedAlbum = $this->service->createOrUpdate($data);

        $this->assertEquals($album->id, $updatedAlbum->id);
        $this->assertEquals('Updated Album', $updatedAlbum->name);
        $this->assertEquals('compilation', $updatedAlbum->album_type);
        $this->assertEquals(15, $updatedAlbum->total_tracks);
        $this->assertEquals(1, Album::count());
    }

    public function test_create_or_update_handles_optional_fields(): void
    {
        $data = [
            'id' => 'spotify_album_123',
            'name' => 'Test Album',
            'uri' => 'spotify:album:123',
            'href' => 'https://api.spotify.com/v1/albums/123',
            'external_urls' => ['spotify' => 'https://open.spotify.com/album/123'],
        ];

        $album = $this->service->createOrUpdate($data);

        $this->assertNull($album->album_type);
        $this->assertNull($album->release_date);
        $this->assertNull($album->total_tracks);
    }

    public function test_sync_artists_syncs_relationship(): void
    {
        $album = Album::factory()->create();
        $artists = Artist::factory()->count(3)->create();
        $artistIds = $artists->pluck('id')->toArray();

        $this->service->syncArtists($album, $artistIds);

        $this->assertEquals(3, $album->artists()->count());
    }

    public function test_sync_artists_replaces_existing_relationships(): void
    {
        $album = Album::factory()->create();
        $oldArtists = Artist::factory()->count(2)->create();
        $album->artists()->attach($oldArtists->pluck('id'));

        $newArtists = Artist::factory()->count(3)->create();
        $this->service->syncArtists($album, $newArtists->pluck('id')->toArray());

        $this->assertEquals(3, $album->artists()->count());
        $this->assertTrue($album->artists()->where('id', $newArtists->first()->id)->exists());
        $this->assertFalse($album->artists()->where('id', $oldArtists->first()->id)->exists());
    }

    public function test_sync_tracks_syncs_relationship(): void
    {
        $album = Album::factory()->create();
        $tracks = Track::factory()->count(10)->create();
        $trackIds = $tracks->pluck('id')->toArray();

        $this->service->syncTracks($album, $trackIds);

        $this->assertEquals(10, $album->tracks()->count());
    }

    public function test_sync_tracks_replaces_existing_relationships(): void
    {
        $album = Album::factory()->create();
        $oldTracks = Track::factory()->count(8)->create();
        $album->tracks()->attach($oldTracks->pluck('id'));

        $newTracks = Track::factory()->count(12)->create();
        $this->service->syncTracks($album, $newTracks->pluck('id')->toArray());

        $this->assertEquals(12, $album->tracks()->count());
        $this->assertTrue($album->tracks()->where('id', $newTracks->first()->id)->exists());
        $this->assertFalse($album->tracks()->where('id', $oldTracks->first()->id)->exists());
    }

    public function test_handles_different_album_types(): void
    {
        $albumTypes = ['album', 'single', 'compilation'];

        foreach ($albumTypes as $type) {
            $data = [
                'id' => 'spotify_album_'.$type,
                'name' => 'Test '.$type,
                'album_type' => $type,
                'uri' => 'spotify:album:'.$type,
                'href' => 'https://api.spotify.com/v1/albums/'.$type,
                'external_urls' => ['spotify' => 'https://open.spotify.com/album/'.$type],
            ];

            $album = $this->service->createOrUpdate($data);
            $this->assertEquals($type, $album->album_type);
        }
    }
}
