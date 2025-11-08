<?php

namespace Tests\Unit\Models;

use App\Models\Artist;
use App\Models\Track;
use App\Models\Album;
use PHPUnit\Framework\TestCase;

class ArtistTest extends TestCase
{
    public function test_artist_has_fillable_attributes(): void
    {
        $artist = new Artist();
        
        $expectedFillable = [
            'spotify_id',
            'name',
            'genres',
            'popularity',
            'followers',
            'images',
            'uri',
            'href',
            'external_url',
        ];
        
        $this->assertEquals($expectedFillable, $artist->getFillable());
    }

    public function test_artist_casts_attributes_correctly(): void
    {
        $artist = new Artist();
        
        $casts = $artist->getCasts();
        
        $this->assertEquals('array', $casts['genres']);
        $this->assertEquals('array', $casts['images']);
        $this->assertEquals('integer', $casts['popularity']);
        $this->assertEquals('integer', $casts['followers']);
    }

    public function test_artist_has_tracks_relationship(): void
    {
        $artist = new Artist();
        
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $artist->tracks()
        );
    }

    public function test_artist_has_albums_relationship(): void
    {
        $artist = new Artist();
        
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $artist->albums()
        );
    }
}
