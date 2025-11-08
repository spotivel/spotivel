<?php

namespace Tests\Unit\Models;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    public function test_album_has_fillable_attributes(): void
    {
        $album = new Album();
        
        $expectedFillable = [
            'spotify_id',
            'name',
            'album_type',
            'release_date',
            'release_date_precision',
            'total_tracks',
            'available_markets',
            'images',
            'uri',
            'href',
            'external_url',
        ];
        
        $this->assertEquals($expectedFillable, $album->getFillable());
    }

    public function test_album_casts_attributes_correctly(): void
    {
        $album = new Album();
        
        $casts = $album->getCasts();
        
        $this->assertEquals('array', $casts['available_markets']);
        $this->assertEquals('array', $casts['images']);
        $this->assertEquals('integer', $casts['total_tracks']);
    }

    public function test_album_has_artists_relationship(): void
    {
        $album = new Album();
        
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $album->artists()
        );
    }

    public function test_album_has_tracks_relationship(): void
    {
        $album = new Album();
        
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $album->tracks()
        );
    }
}
