<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reaction>
 */
class ReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'photo_id' => Photo::factory(),
            'user_id' => User::factory(),
            'emoji' => fake()->randomElement(['❤️', '😍', '😂', '👏', '🎉', '👍']),
        ];
    }
}
