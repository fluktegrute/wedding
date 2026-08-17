<?php

namespace Tests\Feature\Services;

use App\Models\Photo;
use App\Services\GoogleDriveExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithGoogleDrive;
use Tests\TestCase;

class GoogleDriveExporterTest extends TestCase
{
    use InteractsWithGoogleDrive, RefreshDatabase;

    public function test_export_uploads_the_photo_and_records_the_drive_file_id(): void
    {
        Storage::fake('public');
        $this->fakeGoogleDriveCredentials();
        $this->fakeGoogleDriveHttp('uploaded-file-id');

        $photo = Photo::factory()->create(['is_synced_to_gdrive' => false]);
        Storage::disk('public')->put($photo->image_path, 'fake-image-bytes');

        app(GoogleDriveExporter::class)->export($photo);

        $photo->refresh();

        $this->assertTrue($photo->is_synced_to_gdrive);
        $this->assertSame('uploaded-file-id', $photo->gdrive_file_id);
        $this->assertNotNull($photo->gdrive_synced_at);
    }

    public function test_unsync_deletes_the_file_from_drive_and_clears_the_flags(): void
    {
        Storage::fake('public');
        $this->fakeGoogleDriveCredentials();
        $this->fakeGoogleDriveHttp();

        $photo = Photo::factory()->syncedToGdrive()->create();

        app(GoogleDriveExporter::class)->unsync($photo);

        $photo->refresh();

        $this->assertFalse($photo->is_synced_to_gdrive);
        $this->assertNull($photo->gdrive_file_id);
        $this->assertNull($photo->gdrive_synced_at);

        Http::assertSent(fn ($request) => $request->method() === 'DELETE');
    }
}
