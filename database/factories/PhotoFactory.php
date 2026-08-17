<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->unique()->numerify('photo-####').'.jpg';

        return [
            'user_id' => User::factory(),
            'image_path' => 'photos/'.$filename,
            'original_filename' => $filename,
            'description' => fake()->optional()->sentence(),
            'is_featured' => false,
            'is_synced_to_gdrive' => false,
            'gdrive_file_id' => null,
            'gdrive_synced_at' => null,
        ];
    }

    /**
     * Indicate that the photo is featured by an admin.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the photo has been synced to Google Drive.
     */
    public function syncedToGdrive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_synced_to_gdrive' => true,
            'gdrive_file_id' => fake()->uuid(),
            'gdrive_synced_at' => now(),
        ]);
    }
}
