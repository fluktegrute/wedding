<?php

namespace App\Console\Commands;

use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\MediaType;

class BackfillPhotoThumbnails extends Command
{
    protected $signature = 'photos:backfill-thumbnails';

    protected $description = 'Génère la miniature manquante des photos déposées avant l\'ajout du champ thumbnail_path';

    public function handle(): int
    {
        $photos = Photo::query()->whereNull('thumbnail_path')->get();

        if ($photos->isEmpty()) {
            $this->info('Aucune photo sans miniature.');

            return self::SUCCESS;
        }

        $skipped = 0;

        $this->withProgressBar($photos, function (Photo $photo) use (&$skipped): void {
            if (! Storage::disk('public')->exists($photo->image_path)) {
                $skipped++;

                return;
            }

            $photo->thumbnail_path = $this->generateThumbnail($photo);
            $photo->saveQuietly();
        });

        $this->newLine(2);
        $this->info(($photos->count() - $skipped).' miniature(s) générée(s).');

        if ($skipped > 0) {
            $this->warn("{$skipped} photo(s) ignorée(s) : fichier source introuvable sur le disque.");
        }

        return self::SUCCESS;
    }

    private function generateThumbnail(Photo $photo): string
    {
        $image = ImageManager::imagick()
            ->read(Storage::disk('public')->path($photo->image_path))
            ->scaleDown(width: 480);

        $encoded = $image->encode();
        $extension = MediaType::create($encoded->mediaType())->fileExtension()->value;
        $path = 'photos/'.Str::random(40).'.'.$extension;

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }
}
