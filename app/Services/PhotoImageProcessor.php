<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\MediaType;

/**
 * Converts and stores an uploaded photo as two files on the 'public' disk: a full-size
 * version (scaled down to MAX_WIDTH) for the detail view, and a lightweight thumbnail
 * (scaled down to THUMBNAIL_WIDTH) for gallery grids, so grids never ship pixels the
 * browser immediately downsizes.
 */
class PhotoImageProcessor
{
    /** Extensions HEIC/HEIF non lisibles nativement par les navigateurs : converties en JPG au dépôt. */
    private const HEIC_EXTENSIONS = ['heic', 'heif'];

    /** Largeur maximale, en pixels, de la version pleine taille (l'espace disque du serveur n'est pas illimité). */
    private const MAX_WIDTH = 1920;

    /** Largeur de la miniature utilisée dans les grilles de galerie. */
    private const THUMBNAIL_WIDTH = 480;

    /**
     * @return array{image_path: string, thumbnail_path: string}
     */
    public function store(UploadedFile $image): array
    {
        $isHeic = $this->isHeic($image);
        $source = ImageManager::imagick()->read($image->getRealPath());

        return [
            'image_path' => $this->storeVariant(clone $source, self::MAX_WIDTH, $isHeic),
            'thumbnail_path' => $this->storeVariant(clone $source, self::THUMBNAIL_WIDTH, $isHeic),
        ];
    }

    private function storeVariant(ImageInterface $image, int $width, bool $isHeic): string
    {
        $resized = $image->scaleDown(width: $width);

        $encoded = $isHeic ? $resized->toJpeg() : $resized->encode();

        // L'extension de stockage vient du format réellement produit par Imagick, jamais du nom de
        // fichier fourni par le client : ce dernier est falsifiable (ex. "exploit.php" avec un contenu image).
        $extension = MediaType::create($encoded->mediaType())->fileExtension()->value;

        $path = 'photos/'.Str::random(40).'.'.$extension;

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    private function isHeic(UploadedFile $image): bool
    {
        return in_array(Str::lower($image->getClientOriginalExtension()), self::HEIC_EXTENSIONS, true)
            || in_array($image->getMimeType(), ['image/heic', 'image/heif'], true);
    }
}
