<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Thin client for the Google Drive v3 API, authenticated as a service account
 * (credentials in config/services.php, populated from the GOOGLE_DRIVE_* .env
 * variables). No official Google SDK is used: the service-account JWT bearer
 * auth flow and a multipart file upload are both simple enough to implement
 * directly against the REST API, for a fraction of the dependency weight.
 *
 * Setup: create a Google Cloud service account with the Drive API enabled,
 * share the destination Drive folder with its email address (Editor access),
 * and set GOOGLE_DRIVE_CLIENT_EMAIL/GOOGLE_DRIVE_PRIVATE_KEY/GOOGLE_DRIVE_FOLDER_ID.
 */
class GoogleDriveClient
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3/files';

    private const FILES_URL = 'https://www.googleapis.com/drive/v3/files';

    /** Scope limited to files created by this app, rather than full Drive access. */
    private const SCOPE = 'https://www.googleapis.com/auth/drive.file';

    /**
     * Upload a file's contents to the configured Drive folder and return the
     * resulting Drive file ID.
     */
    public function upload(string $filename, string $mimeType, string $contents): string
    {
        $boundary = Str::random(32);

        $metadata = json_encode([
            'name' => $filename,
            'parents' => [$this->folderId()],
        ], JSON_THROW_ON_ERROR);

        $body = "--{$boundary}\r\n"
            ."Content-Type: application/json; charset=UTF-8\r\n\r\n"
            ."{$metadata}\r\n"
            ."--{$boundary}\r\n"
            ."Content-Type: {$mimeType}\r\n\r\n"
            ."{$contents}\r\n"
            ."--{$boundary}--";

        $response = Http::withToken($this->accessToken())
            ->withBody($body, "multipart/related; boundary={$boundary}")
            ->post(self::UPLOAD_URL.'?uploadType=multipart&fields=id');

        if ($response->failed()) {
            throw new RuntimeException("Google Drive upload failed: {$response->body()}");
        }

        return $response->json('id');
    }

    /**
     * Delete a file from Drive. Missing files (already deleted on the Drive
     * side) are treated as success.
     */
    public function delete(string $fileId): void
    {
        $response = Http::withToken($this->accessToken())
            ->delete(self::FILES_URL."/{$fileId}");

        if ($response->failed() && $response->status() !== 404) {
            throw new RuntimeException("Google Drive delete failed: {$response->body()}");
        }
    }

    private function folderId(): string
    {
        return config('services.google_drive.folder_id');
    }

    /**
     * Exchange the service account's signed JWT for a short-lived OAuth2
     * access token, cached until shortly before it expires.
     */
    private function accessToken(): string
    {
        return Cache::remember('google-drive-access-token', 3000, function () {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $this->signedJwt(),
            ]);

            if ($response->failed()) {
                throw new RuntimeException("Google Drive authentication failed: {$response->body()}");
            }

            return $response->json('access_token');
        });
    }

    private function signedJwt(): string
    {
        $now = time();

        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));

        $claims = $this->base64UrlEncode(json_encode([
            'iss' => config('services.google_drive.client_email'),
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URL,
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));

        openssl_sign("{$header}.{$claims}", $signature, $this->privateKey(), OPENSSL_ALGO_SHA256);

        return "{$header}.{$claims}.".$this->base64UrlEncode($signature);
    }

    private function privateKey(): string
    {
        return str_replace('\n', "\n", config('services.google_drive.private_key'));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
