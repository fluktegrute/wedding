<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Http;
use RuntimeException;

trait InteractsWithGoogleDrive
{
    /**
     * Configure a throwaway service-account key pair so GoogleDriveClient can sign a
     * JWT without needing real Google Cloud credentials.
     */
    protected function fakeGoogleDriveCredentials(): void
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);

        if ($key === false) {
            throw new RuntimeException('Failed to generate a test RSA key pair.');
        }

        openssl_pkey_export($key, $privateKey);

        config([
            'services.google_drive.client_email' => 'wedding-app@test.iam.gserviceaccount.com',
            'services.google_drive.private_key' => $privateKey,
            'services.google_drive.folder_id' => 'test-folder-id',
        ]);
    }

    /**
     * Fake the Google OAuth2 token endpoint and the Drive v3 upload/delete endpoints.
     */
    protected function fakeGoogleDriveHttp(string $fileId = 'drive-file-id'): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'fake-access-token', 'expires_in' => 3600]),
            'www.googleapis.com/upload/drive/v3/files*' => Http::response(['id' => $fileId]),
            'www.googleapis.com/drive/v3/files/*' => Http::response(null, 204),
        ]);
    }
}
