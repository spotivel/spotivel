<?php

namespace Database\Factories;

use App\Models\Album;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlbumFactory extends Factory
{
    protected $model = Album::class;

    public function definition(): array
    {
        return [
            'spotify_id' => 'spotify_'.fake()->unique()->uuid(),
            'name' => fake()->sentence(2),
            'album_type' => fake()->randomElement(['album', 'single', 'compilation']),
            'release_date' => fake()->date(),
            'release_date_precision' => 'day',
            'total_tracks' => fake()->numberBetween(1, 20),
            'uri' => 'spotify:album:'.fake()->uuid(),
            'href' => fake()->url(),
            'external_url' => fake()->url(),
        ];
    }
}
