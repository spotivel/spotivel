<?php

namespace Database\Factories;

use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackFactory extends Factory
{
    protected $model = Track::class;

    public function definition(): array
    {
        return [
            'spotify_id' => 'spotify_'.fake()->unique()->uuid(),
            'name' => fake()->sentence(3),
            'duration_ms' => fake()->numberBetween(60000, 300000),
            'explicit' => fake()->boolean(20),
            'disc_number' => fake()->numberBetween(1, 2),
            'track_number' => fake()->numberBetween(1, 15),
            'popularity' => fake()->numberBetween(0, 100),
            'preview_url' => fake()->url(),
            'uri' => 'spotify:track:'.fake()->uuid(),
            'href' => fake()->url(),
            'external_url' => fake()->url(),
            'is_local' => false,
        ];
    }
}
