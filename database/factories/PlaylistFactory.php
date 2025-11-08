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
            'spotify_id' => 'spotify_'.fake()->unique()->uuid(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'public' => fake()->boolean(80),
            'collaborative' => fake()->boolean(10),
            'total_tracks' => fake()->numberBetween(1, 100),
            'uri' => 'spotify:playlist:'.fake()->uuid(),
            'href' => fake()->url(),
            'external_url' => fake()->url(),
            'owner_id' => 'spotify_user_'.fake()->uuid(),
            'owner_name' => fake()->name(),
        ];
    }
}
