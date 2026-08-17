<?php

namespace Tests\Feature\Photos;

use App\Livewire\Photos\Show;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_detail_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $photo = Photo::factory()->create();

        $this->get("/photos/{$photo->id}")->assertOk();
    }

    public function test_it_shows_the_photo_description_and_author(): void
    {
        $this->actingAs(User::factory()->create());

        $author = User::factory()->create(['name' => 'Jean Dupont']);
        $photo = Photo::factory()->for($author)->create(['description' => 'Le lancer de bouquet']);

        Livewire::test(Show::class, ['photo' => $photo])
            ->assertSee('Jean Dupont')
            ->assertSee('Le lancer de bouquet');
    }

    public function test_it_lists_comments_with_their_author(): void
    {
        $this->actingAs(User::factory()->create());

        $commenter = User::factory()->create(['name' => 'Marie Curie']);
        $photo = Photo::factory()->create();

        Comment::factory()->for($photo)->for($commenter)->create(['body' => 'Quelle belle photo !']);

        Livewire::test(Show::class, ['photo' => $photo])
            ->assertSee('Marie Curie')
            ->assertSee('Quelle belle photo !');
    }

    public function test_it_shows_reactions_grouped_by_emoji_with_their_count(): void
    {
        $this->actingAs(User::factory()->create());

        $photo = Photo::factory()->create();

        Reaction::factory()->for($photo)->create(['emoji' => '❤️']);
        Reaction::factory()->for($photo)->create(['emoji' => '❤️']);
        Reaction::factory()->for($photo)->create(['emoji' => '🎉']);

        Livewire::test(Show::class, ['photo' => $photo])
            ->assertSeeHtml('❤️ 2')
            ->assertSeeHtml('🎉 1');
    }

    public function test_guest_can_add_a_comment(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $photo = Photo::factory()->create();

        Livewire::test(Show::class, ['photo' => $photo])
            ->set('newComment', 'Superbe moment !')
            ->call('addComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'photo_id' => $photo->id,
            'user_id' => $user->id,
            'body' => 'Superbe moment !',
        ]);
    }

    public function test_comment_body_is_required(): void
    {
        $this->actingAs(User::factory()->create());

        $photo = Photo::factory()->create();

        Livewire::test(Show::class, ['photo' => $photo])
            ->set('newComment', '')
            ->call('addComment')
            ->assertHasErrors(['newComment' => 'required']);

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_guest_can_react_to_a_photo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $photo = Photo::factory()->create();

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('toggleReaction', '❤️')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reactions', [
            'photo_id' => $photo->id,
            'user_id' => $user->id,
            'emoji' => '❤️',
        ]);
    }

    public function test_reacting_again_with_the_same_emoji_removes_the_reaction(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $photo = Photo::factory()->create();

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('toggleReaction', '❤️')
            ->call('toggleReaction', '❤️');

        $this->assertDatabaseCount('reactions', 0);
    }

    public function test_reacting_with_a_different_emoji_changes_the_reaction(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $photo = Photo::factory()->create();

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('toggleReaction', '❤️')
            ->call('toggleReaction', '🎉');

        $this->assertDatabaseCount('reactions', 1);

        $this->assertDatabaseHas('reactions', [
            'photo_id' => $photo->id,
            'user_id' => $user->id,
            'emoji' => '🎉',
        ]);
    }

    public function test_a_guest_can_only_have_one_reaction_on_a_photo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $photo = Photo::factory()->create();

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('toggleReaction', '❤️')
            ->call('toggleReaction', '😍');

        $this->assertSame(1, Reaction::where('photo_id', $photo->id)->where('user_id', $user->id)->count());
    }

    public function test_an_unknown_emoji_is_ignored(): void
    {
        $this->actingAs(User::factory()->create());

        $photo = Photo::factory()->create();

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('toggleReaction', 'not-an-emoji');

        $this->assertDatabaseCount('reactions', 0);
    }

    public function test_the_author_can_delete_their_photo(): void
    {
        Storage::fake('public');

        $author = User::factory()->create();

        $this->actingAs($author);

        $path = UploadedFile::fake()->image('mariage.jpg')->store('photos', 'public');
        $photo = Photo::factory()->for($author)->create(['image_path' => $path]);

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('deletePhoto')
            ->assertRedirect(route('photos.index'));

        $this->assertDatabaseMissing('photos', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_an_admin_can_delete_someone_elses_photo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $author = User::factory()->create();

        $this->actingAs($admin);

        $photo = Photo::factory()->for($author)->create();

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('deletePhoto')
            ->assertRedirect(route('photos.index'));

        $this->assertDatabaseMissing('photos', ['id' => $photo->id]);
    }

    public function test_another_guest_cannot_delete_someone_elses_photo(): void
    {
        $author = User::factory()->create();
        $otherGuest = User::factory()->create();

        $this->actingAs($otherGuest);

        $photo = Photo::factory()->for($author)->create();

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('deletePhoto')
            ->assertForbidden();

        $this->assertDatabaseHas('photos', ['id' => $photo->id]);
    }

    public function test_deleting_a_photo_also_removes_its_comments_and_reactions(): void
    {
        $author = User::factory()->create();

        $this->actingAs($author);

        $photo = Photo::factory()->for($author)->create();
        Comment::factory()->for($photo)->create();
        Reaction::factory()->for($photo)->create();

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('deletePhoto');

        $this->assertDatabaseCount('comments', 0);
        $this->assertDatabaseCount('reactions', 0);
    }

    public function test_the_comment_author_can_delete_their_comment(): void
    {
        $author = User::factory()->create();
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo)->for($author)->create();

        $this->actingAs($author);

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('deleteComment', $comment->id);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_an_admin_can_delete_someone_elses_comment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo)->create();

        $this->actingAs($admin);

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('deleteComment', $comment->id);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_another_guest_cannot_delete_someone_elses_comment(): void
    {
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo)->create();

        $this->actingAs(User::factory()->create());

        Livewire::test(Show::class, ['photo' => $photo])
            ->call('deleteComment', $comment->id)
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }
}
