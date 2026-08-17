<?php

namespace Tests\Feature\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_guest_can_view_and_create_comments(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $this->assertTrue($user->can('viewAny', Comment::class));
        $this->assertTrue($user->can('view', $comment));
        $this->assertTrue($user->can('create', Comment::class));
    }

    public function test_author_can_update_and_delete_their_comment(): void
    {
        $author = User::factory()->create();
        $comment = Comment::factory()->for($author)->create();

        $this->assertTrue($author->can('update', $comment));
        $this->assertTrue($author->can('delete', $comment));
    }

    public function test_another_guest_cannot_update_or_delete_someone_elses_comment(): void
    {
        $author = User::factory()->create();
        $otherGuest = User::factory()->create();
        $comment = Comment::factory()->for($author)->create();

        $this->assertFalse($otherGuest->can('update', $comment));
        $this->assertFalse($otherGuest->can('delete', $comment));
    }

    public function test_admin_can_delete_but_not_update_someone_elses_comment(): void
    {
        $author = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $comment = Comment::factory()->for($author)->create();

        $this->assertTrue($admin->can('delete', $comment));
        $this->assertFalse($admin->can('update', $comment));
    }
}
