<?php

namespace Tests\Feature\Photos;

use App\Livewire\Photos\Upload;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/photos/create')->assertOk();
    }

    public function test_guest_can_upload_a_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test(Upload::class)
            ->set('image', UploadedFile::fake()->image('mariage.jpg'))
            ->set('description', 'Le premier danse')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseCount('photos', 1);

        $photo = Photo::first();

        $this->assertSame($user->id, $photo->user_id);
        $this->assertSame('mariage.jpg', $photo->original_filename);
        $this->assertSame('Le premier danse', $photo->description);

        Storage::disk('public')->assertExists($photo->image_path);
    }

    public function test_description_is_optional(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $response = Livewire::test(Upload::class)
            ->set('image', UploadedFile::fake()->image('mariage.jpg'))
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertNull(Photo::first()->description);
    }

    public function test_image_is_required(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $response = Livewire::test(Upload::class)
            ->set('image', null)
            ->call('save');

        $response->assertHasErrors(['image' => 'required']);

        $this->assertDatabaseCount('photos', 0);
    }

    public function test_non_image_files_are_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $response = Livewire::test(Upload::class)
            ->set('image', UploadedFile::fake()->create('document.pdf', 100))
            ->call('save');

        $response->assertHasErrors(['image' => 'image']);

        $this->assertDatabaseCount('photos', 0);
    }

    public function test_heic_photos_are_converted_to_jpg_on_upload(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $response = Livewire::test(Upload::class)
            ->set('image', UploadedFile::fake()->image('iphone.heic'))
            ->call('save');

        $response->assertHasNoErrors();

        $photo = Photo::first();

        $this->assertSame('iphone.heic', $photo->original_filename);
        $this->assertStringEndsWith('.jpg', $photo->image_path);

        Storage::disk('public')->assertExists($photo->image_path);
    }

    public function test_large_photos_are_scaled_down_to_a_max_width_of_1920px_on_upload(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $response = Livewire::test(Upload::class)
            ->set('image', UploadedFile::fake()->image('mariage.jpg', 3000, 2000))
            ->call('save');

        $response->assertHasNoErrors();

        $photo = Photo::first();

        $dimensions = getimagesize(Storage::disk('public')->path($photo->image_path));

        $this->assertNotFalse($dimensions);
        $this->assertSame(1920, $dimensions[0]);
        $this->assertSame(1280, $dimensions[1]);
    }

    public function test_small_photos_are_not_upscaled_on_upload(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $response = Livewire::test(Upload::class)
            ->set('image', UploadedFile::fake()->image('mariage.jpg', 800, 600))
            ->call('save');

        $response->assertHasNoErrors();

        $photo = Photo::first();

        $dimensions = getimagesize(Storage::disk('public')->path($photo->image_path));

        $this->assertNotFalse($dimensions);
        $this->assertSame(800, $dimensions[0]);
        $this->assertSame(600, $dimensions[1]);
    }

    public function test_guest_can_tag_a_photo_at_upload(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $ceremony = Tag::factory()->create();
        $cocktail = Tag::factory()->create();

        $response = Livewire::test(Upload::class)
            ->set('image', UploadedFile::fake()->image('mariage.jpg'))
            ->set('tags', [$ceremony->id, $cocktail->id])
            ->call('save');

        $response->assertHasNoErrors();

        $photo = Photo::first();

        $this->assertSame([$ceremony->id, $cocktail->id], $photo->tags()->pluck('tags.id')->all());
    }

    public function test_tags_are_optional(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $response = Livewire::test(Upload::class)
            ->set('image', UploadedFile::fake()->image('mariage.jpg'))
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertCount(0, Photo::first()->tags);
    }

    public function test_unknown_tag_ids_are_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $response = Livewire::test(Upload::class)
            ->set('image', UploadedFile::fake()->image('mariage.jpg'))
            ->set('tags', [9999])
            ->call('save');

        $response->assertHasErrors(['tags.0' => 'exists']);

        $this->assertDatabaseCount('photos', 0);
    }
}
