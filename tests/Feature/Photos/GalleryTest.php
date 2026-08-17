<?php

namespace Tests\Feature\Photos;

use App\Livewire\Photos\Index;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/photos')->assertOk();
    }

    public function test_gallery_lists_photos_from_every_guest(): void
    {
        $this->actingAs(User::factory()->create());

        $ownPhoto = Photo::factory()->create(['original_filename' => 'mine.jpg']);
        $otherGuestPhoto = Photo::factory()->create(['original_filename' => 'not-mine.jpg']);

        Livewire::test(Index::class)
            ->assertSee($ownPhoto->original_filename)
            ->assertSee($otherGuestPhoto->original_filename);
    }

    public function test_gallery_shows_empty_state_without_photos(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->assertSee('Aucune photo pour le moment');
    }

    public function test_guest_can_navigate_to_photo_detail_from_the_gallery(): void
    {
        $this->actingAs(User::factory()->create());

        $photo = Photo::factory()->create();

        Livewire::test(Index::class)
            ->assertSeeHtml(route('photos.show', $photo));
    }

    public function test_gallery_can_be_filtered_by_tag(): void
    {
        $this->actingAs(User::factory()->create());

        $ceremony = Tag::factory()->create();
        $cocktail = Tag::factory()->create();

        $ceremonyPhoto = Photo::factory()->create(['original_filename' => 'ceremony.jpg']);
        $ceremonyPhoto->tags()->attach($ceremony);

        $cocktailPhoto = Photo::factory()->create(['original_filename' => 'cocktail.jpg']);
        $cocktailPhoto->tags()->attach($cocktail);

        Livewire::test(Index::class)
            ->set('tag', $ceremony->id)
            ->assertSee($ceremonyPhoto->original_filename)
            ->assertDontSee($cocktailPhoto->original_filename);
    }
}
