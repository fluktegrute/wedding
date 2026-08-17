<?php

namespace Tests\Feature\Policies;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotoPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_guest_can_view_and_create_photos(): void
    {
        $user = User::factory()->create();
        $photo = Photo::factory()->create();

        $this->assertTrue($user->can('viewAny', Photo::class));
        $this->assertTrue($user->can('view', $photo));
        $this->assertTrue($user->can('create', Photo::class));
    }

    public function test_owner_can_update_and_delete_their_photo(): void
    {
        $owner = User::factory()->create();
        $photo = Photo::factory()->for($owner)->create();

        $this->assertTrue($owner->can('update', $photo));
        $this->assertTrue($owner->can('delete', $photo));
    }

    public function test_another_guest_cannot_update_or_delete_someone_elses_photo(): void
    {
        $owner = User::factory()->create();
        $otherGuest = User::factory()->create();
        $photo = Photo::factory()->for($owner)->create();

        $this->assertFalse($otherGuest->can('update', $photo));
        $this->assertFalse($otherGuest->can('delete', $photo));
    }

    public function test_admin_can_update_and_delete_any_photo(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $photo = Photo::factory()->for($owner)->create();

        $this->assertTrue($admin->can('update', $photo));
        $this->assertTrue($admin->can('delete', $photo));
    }

    public function test_only_an_admin_can_feature_a_photo(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $photo = Photo::factory()->for($owner)->create();

        $this->assertFalse($owner->can('feature', $photo));
        $this->assertTrue($admin->can('feature', $photo));
    }

    public function test_only_an_admin_can_sync_a_photo_to_gdrive(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $photo = Photo::factory()->for($owner)->create();

        $this->assertFalse($owner->can('syncToGdrive', $photo));
        $this->assertTrue($admin->can('syncToGdrive', $photo));
    }
}
