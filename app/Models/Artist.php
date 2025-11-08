<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artist extends Model
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
        'genres',
        'popularity',
        'followers',
        'images',
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
        'genres' => 'array',
        'images' => 'array',
        'popularity' => 'integer',
        'followers' => 'integer',
    ];

    /**
     * Get the tracks for this artist.
     */
    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class);
    }

    /**
     * Get the albums for this artist.
     */
    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class);
    }
}
