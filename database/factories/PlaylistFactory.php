<?php

namespace Database\Factories;

use App\Models\Playlist;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlaylistFactory extends Factory
{
    protected $model = Playlist::class;

    public function definition(): array
    {
        return [
            'spotify_id' => fake()->unique()->regexify('[A-Za-z0-9]{22}'),
            'name' => implode(' ', fake()->words(4)),
            'description' => fake()->paragraph(),
            'public' => fake()->boolean(80),
            'collaborative' => fake()->boolean(10),
            'total_tracks' => fake()->numberBetween(1, 100),
            'uri' => 'spotify:playlist:'.fake()->uuid(),
            'href' => fake()->url(),
            'external_url' => fake()->url(),
            'owner_id' => fake()->regexify('[A-Za-z0-9]{20}'),
            'owner_name' => fake()->name(),
        ];
    }
}
