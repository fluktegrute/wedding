<?php

namespace Tests\Feature\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_guest_can_view_tags(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $this->assertTrue($user->can('viewAny', Tag::class));
        $this->assertTrue($user->can('view', $tag));
    }

    public function test_only_an_admin_can_manage_tags(): void
    {
        $guest = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $tag = Tag::factory()->create();

        $this->assertFalse($guest->can('create', Tag::class));
        $this->assertFalse($guest->can('update', $tag));
        $this->assertFalse($guest->can('delete', $tag));

        $this->assertTrue($admin->can('create', Tag::class));
        $this->assertTrue($admin->can('update', $tag));
        $this->assertTrue($admin->can('delete', $tag));
    }
}
