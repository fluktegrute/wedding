<?php

namespace Tests\Feature\Policies;

use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReactionPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_guest_can_view_and_create_reactions(): void
    {
        $user = User::factory()->create();
        $reaction = Reaction::factory()->create();

        $this->assertTrue($user->can('viewAny', Reaction::class));
        $this->assertTrue($user->can('view', $reaction));
        $this->assertTrue($user->can('create', Reaction::class));
    }

    public function test_author_can_update_and_delete_their_reaction(): void
    {
        $author = User::factory()->create();
        $reaction = Reaction::factory()->for($author)->create();

        $this->assertTrue($author->can('update', $reaction));
        $this->assertTrue($author->can('delete', $reaction));
    }

    public function test_another_guest_cannot_update_or_delete_someone_elses_reaction(): void
    {
        $author = User::factory()->create();
        $otherGuest = User::factory()->create();
        $reaction = Reaction::factory()->for($author)->create();

        $this->assertFalse($otherGuest->can('update', $reaction));
        $this->assertFalse($otherGuest->can('delete', $reaction));
    }

    public function test_admin_can_delete_but_not_update_someone_elses_reaction(): void
    {
        $author = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $reaction = Reaction::factory()->for($author)->create();

        $this->assertTrue($admin->can('delete', $reaction));
        $this->assertFalse($admin->can('update', $reaction));
    }
}
