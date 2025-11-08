<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Playlist extends Model
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
        'description',
        'public',
        'collaborative',
        'total_tracks',
        'images',
        'uri',
        'href',
        'external_url',
        'owner_id',
        'owner_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'public' => 'boolean',
        'collaborative' => 'boolean',
        'total_tracks' => 'integer',
        'images' => 'array',
    ];

    /**
     * Get the tracks for this playlist.
     */
    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class)
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('position');
    }
}
