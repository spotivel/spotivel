<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Track extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
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
        'is_interesting',
        'available_markets',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration_ms' => 'integer',
        'explicit' => 'boolean',
        'disc_number' => 'integer',
        'track_number' => 'integer',
        'popularity' => 'integer',
        'is_local' => 'boolean',
        'is_interesting' => 'boolean',
        'available_markets' => 'array',
    ];

    /**
     * Get the artists for this track.
     */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class);
    }

    /**
     * Get the albums for this track.
     */
    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class);
    }

    /**
     * Get the playlists that contain this track.
     */
    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class)
            ->withPivot('position')
            ->withTimestamps();
    }
}
