<?php

namespace App\Services;

use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Exports selected photos to the wedding's Google Drive folder using a
 * Google Cloud service account (credentials in config/services.php, see
 * GoogleDriveClient for the auth/upload implementation and setup steps).
 */
class GoogleDriveExporter
{
    public function __construct(private readonly GoogleDriveClient $client) {}

    public function export(Photo $photo): void
    {
        $mimeType = Storage::disk('public')->mimeType($photo->image_path);

        throw_if($mimeType === false, new RuntimeException("Could not determine the MIME type of [{$photo->image_path}]."));

        $fileId = $this->client->upload(
            filename: $photo->original_filename,
            mimeType: $mimeType,
            contents: Storage::disk('public')->get($photo->image_path),
        );

        $photo->is_synced_to_gdrive = true;
        $photo->gdrive_file_id = $fileId;
        $photo->gdrive_synced_at = Carbon::now();
        $photo->save();
    }

    public function unsync(Photo $photo): void
    {
        if ($photo->gdrive_file_id !== null) {
            $this->client->delete($photo->gdrive_file_id);
        }

        $photo->is_synced_to_gdrive = false;
        $photo->gdrive_file_id = null;
        $photo->gdrive_synced_at = null;
        $photo->save();
    }
}
