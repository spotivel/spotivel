<?php

namespace Tests\Unit\Models;

use App\Models\Artist;
use App\Models\Track;
use App\Models\Album;
use PHPUnit\Framework\TestCase;

class TrackTest extends TestCase
{
    public function test_track_has_fillable_attributes(): void
    {
        $track = new Track();
        
        $expectedFillable = [
            'spotify_id',
            'name',
            'duration_ms',
            'explicit',
            'disc_number',
            'track_number',
            'popularity',
            'preview_url',
            'uri',
            'href',
            'external_url',
            'is_local',
            'available_markets',
        ];
        
        $this->assertEquals($expectedFillable, $track->getFillable());
    }

    public function test_track_casts_attributes_correctly(): void
    {
        $track = new Track();
        
        $casts = $track->getCasts();
        
        $this->assertEquals('integer', $casts['duration_ms']);
        $this->assertEquals('boolean', $casts['explicit']);
        $this->assertEquals('integer', $casts['disc_number']);
        $this->assertEquals('integer', $casts['track_number']);
        $this->assertEquals('integer', $casts['popularity']);
        $this->assertEquals('boolean', $casts['is_local']);
        $this->assertEquals('array', $casts['available_markets']);
    }

    public function test_track_has_artists_relationship(): void
    {
        $track = new Track();
        
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $track->artists()
        );
    }

    public function test_track_has_albums_relationship(): void
    {
        $track = new Track();
        
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $track->albums()
        );
    }
}
