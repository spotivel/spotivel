<?php

namespace Database\Factories;

use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtistFactory extends Factory
{
    protected $model = Artist::class;

    public function definition(): array
    {
        return [
            'spotify_id' => 'spotify_'.fake()->unique()->uuid(),
            'name' => fake()->name(),
            'popularity' => fake()->numberBetween(0, 100),
            'followers' => fake()->numberBetween(1000, 10000000),
            'uri' => 'spotify:artist:'.fake()->uuid(),
            'href' => fake()->url(),
            'external_url' => fake()->url(),
        ];
    }
}
