<?php

namespace Database\Seeders;

use App\Models\Photo;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_admin' => true,
        ]);

        $guests = User::factory(9)->create();

        $users = $guests->push($admin);

        $photos = $users->flatMap(
            fn (User $user) => Photo::factory(random_int(1, 3))->for($user)->create()
        );

        $photos->each(function (Photo $photo) use ($users) {
            $commenters = $users->random(random_int(0, 3));

            foreach ($commenters as $commenter) {
                $photo->comments()->create([
                    'user_id' => $commenter->id,
                    'body' => fake()->sentence(),
                ]);
            }

            $reactors = $users->random(random_int(0, 5));

            foreach ($reactors as $reactor) {
                Reaction::factory()->for($photo)->for($reactor)->create();
            }
        });
    }
}
