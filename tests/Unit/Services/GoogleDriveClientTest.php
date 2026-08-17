<?php

namespace Tests\Unit\Services;

use App\Services\GoogleDriveClient;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\InteractsWithGoogleDrive;
use Tests\TestCase;

class GoogleDriveClientTest extends TestCase
{
    use InteractsWithGoogleDrive;

    public function test_upload_returns_the_drive_file_id(): void
    {
        $this->fakeGoogleDriveCredentials();
        $this->fakeGoogleDriveHttp('uploaded-file-id');

        $fileId = (new GoogleDriveClient)->upload('mariage.jpg', 'image/jpeg', 'fake-bytes');

        $this->assertSame('uploaded-file-id', $fileId);

        Http::assertSent(fn ($request) => $request->url() === 'https://oauth2.googleapis.com/token'
            && $request['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer');

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://www.googleapis.com/upload/drive/v3/files')
            && str_contains($request->body(), 'fake-bytes')
            && str_contains($request->body(), '"parents":["test-folder-id"]'));
    }

    public function test_the_access_token_is_cached_across_calls(): void
    {
        $this->fakeGoogleDriveCredentials();
        $this->fakeGoogleDriveHttp();

        $client = new GoogleDriveClient;
        $client->upload('a.jpg', 'image/jpeg', 'content-a');
        $client->upload('b.jpg', 'image/jpeg', 'content-b');

        Http::assertSentCount(3);
    }

    public function test_delete_calls_the_drive_api(): void
    {
        $this->fakeGoogleDriveCredentials();
        $this->fakeGoogleDriveHttp();

        (new GoogleDriveClient)->delete('some-file-id');

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && $request->url() === 'https://www.googleapis.com/drive/v3/files/some-file-id');
    }
}
