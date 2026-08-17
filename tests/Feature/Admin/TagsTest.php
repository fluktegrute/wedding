<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Tags;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TagsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_the_admin_tags_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/tags')->assertForbidden();
    }

    public function test_admin_can_access_the_admin_tags_page(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->get('/admin/tags')->assertOk();
    }

    public function test_admin_can_create_a_tag(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Tags::class)
            ->set('name', 'Cérémonie')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tags', ['name' => 'Cérémonie']);
    }

    public function test_tag_name_must_be_unique(): void
    {
        Tag::factory()->create(['name' => 'Cérémonie']);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Tags::class)
            ->set('name', 'Cérémonie')
            ->call('create')
            ->assertHasErrors(['name' => 'unique']);

        $this->assertDatabaseCount('tags', 1);
    }

    public function test_admin_can_rename_a_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Cocktail']);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Tags::class)
            ->call('edit', $tag)
            ->set('name', 'Vin d’honneur')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertSame('Vin d’honneur', $tag->fresh()->name);
    }

    public function test_renaming_a_tag_to_its_own_name_is_allowed(): void
    {
        $tag = Tag::factory()->create(['name' => 'Cocktail']);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Tags::class)
            ->call('edit', $tag)
            ->set('name', 'Cocktail')
            ->call('update')
            ->assertHasNoErrors();
    }

    public function test_admin_can_delete_a_tag_and_it_is_detached_from_photos(): void
    {
        $tag = Tag::factory()->create();
        $photo = Photo::factory()->create();
        $photo->tags()->attach($tag);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Tags::class)
            ->call('delete', $tag)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
        $this->assertCount(0, $photo->tags()->get());
    }

    public function test_guest_cannot_manage_tags_via_the_livewire_component(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Tags::class)
            ->set('name', 'Cérémonie')
            ->call('create')
            ->assertForbidden();
    }
}
