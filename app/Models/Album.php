<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Album extends Model
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
        'album_type',
        'release_date',
        'release_date_precision',
        'total_tracks',
        'uri',
        'href',
        'external_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_tracks' => 'integer',
    ];

    /**
     * Get the artists for this album.
     */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class);
    }

    /**
     * Get the tracks for this album.
     */
    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class);
    }
}
