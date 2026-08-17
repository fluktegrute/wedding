<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Photos;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithGoogleDrive;
use Tests\TestCase;

class PhotosTest extends TestCase
{
    use InteractsWithGoogleDrive, RefreshDatabase;

    public function test_guest_cannot_access_the_admin_photos_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/photos')->assertForbidden();
    }

    public function test_guest_of_the_admin_photos_livewire_component_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Photos::class)
            ->call('toggleFeatured', Photo::factory()->create())
            ->assertForbidden();
    }

    public function test_admin_can_access_the_admin_photos_page(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->get('/admin/photos')->assertOk();
    }

    public function test_admin_can_toggle_a_photo_as_featured(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $photo = Photo::factory()->create(['is_featured' => false]);

        $this->actingAs($admin);

        Livewire::test(Photos::class)
            ->call('toggleFeatured', $photo)
            ->assertHasNoErrors();

        $this->assertTrue($photo->fresh()->is_featured);

        Livewire::test(Photos::class)
            ->call('toggleFeatured', $photo)
            ->assertHasNoErrors();

        $this->assertFalse($photo->fresh()->is_featured);
    }

    public function test_admin_can_select_and_unselect_a_photo_for_gdrive_export(): void
    {
        Storage::fake('public');
        $this->fakeGoogleDriveCredentials();
        $this->fakeGoogleDriveHttp('drive-file-id');

        $admin = User::factory()->create(['is_admin' => true]);
        $photo = Photo::factory()->create(['is_synced_to_gdrive' => false]);
        Storage::disk('public')->put($photo->image_path, 'fake-image-bytes');

        $this->actingAs($admin);

        Livewire::test(Photos::class)
            ->call('toggleSync', $photo)
            ->assertHasNoErrors();

        $photo->refresh();

        $this->assertTrue($photo->is_synced_to_gdrive);
        $this->assertSame('drive-file-id', $photo->gdrive_file_id);
        $this->assertNotNull($photo->gdrive_synced_at);

        Livewire::test(Photos::class)
            ->call('toggleSync', $photo)
            ->assertHasNoErrors();

        $photo->refresh();

        $this->assertFalse($photo->is_synced_to_gdrive);
        $this->assertNull($photo->gdrive_file_id);
        $this->assertNull($photo->gdrive_synced_at);
    }

    public function test_photos_can_be_filtered(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $featured = Photo::factory()->create(['is_featured' => true]);
        $notFeatured = Photo::factory()->create(['is_featured' => false]);

        $this->actingAs($admin);

        Livewire::test(Photos::class)
            ->set('filter', 'featured')
            ->assertSee($featured->original_filename)
            ->assertDontSee($notFeatured->original_filename);
    }
}
