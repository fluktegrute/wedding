<?php

namespace App\Jobs;

use App\Models\Photo;
use App\Services\GoogleDriveExporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Exports or unsyncs a photo from Google Drive in the background: the Drive API call is a
 * slow, third-party HTTP request that would otherwise block the admin's request/response
 * cycle in App\Livewire\Admin\Photos::toggleSync().
 */
class SyncPhotoWithGoogleDriveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Photo $photo,
        public readonly bool $export,
    ) {}

    public function handle(GoogleDriveExporter $exporter): void
    {
        if ($this->export) {
            $exporter->export($this->photo);
        } else {
            $exporter->unsync($this->photo);
        }
    }

    public function failed(Throwable $exception): void
    {
        report($exception);
    }
}
