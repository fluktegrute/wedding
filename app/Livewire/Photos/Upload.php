<?php

namespace App\Livewire\Photos;

use App\Models\Photo;
use App\Models\Tag;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Déposer une photo')]
class Upload extends Component
{
    use WithFileUploads;

    /** Extensions HEIC/HEIF non lisibles nativement par les navigateurs : converties en JPG au dépôt. */
    private const HEIC_EXTENSIONS = ['heic', 'heif'];

    /** Largeur maximale, en pixels, des photos stockées (l'espace disque du serveur n'est pas illimité). */
    private const MAX_WIDTH = 1920;

    #[Validate('required|image|max:10240')]
    public ?UploadedFile $image = null;

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    /** @var list<int> */
    #[Validate('array')]
    public array $tags = [];

    /**
     * Store the uploaded photo for the authenticated guest.
     */
    public function save(): void
    {
        $this->authorize('create', Photo::class);

        $this->validate([
            ...$this->getRules(),
            'tags.*' => 'exists:tags,id',
        ]);

        $path = $this->storeImage($this->image);

        $photo = Auth::user()->photos()->create([
            'image_path' => $path,
            'original_filename' => $this->image->getClientOriginalName(),
            'description' => $this->description ?: null,
        ]);

        $photo->tags()->sync($this->tags);

        Flux::toast(variant: 'success', text: __('Photo ajoutée avec succès.'));

        $this->reset(['image', 'description', 'tags']);
    }

    public function render(): View
    {
        return view('livewire.photos.upload', [
            'availableTags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store the uploaded image, converting HEIC/HEIF photos (typically from iPhones) to JPG
     * so they display correctly in every browser, and scaling every photo down to a maximum
     * width of 1920px to keep disk usage under control.
     */
    private function storeImage(UploadedFile $image): string
    {
        $isHeic = $this->isHeic($image);

        $resized = ImageManager::imagick()
            ->read($image->getRealPath())
            ->scaleDown(width: self::MAX_WIDTH);

        $encoded = $isHeic ? $resized->toJpeg() : $resized->encode();

        $extension = $isHeic ? 'jpg' : Str::lower($image->getClientOriginalExtension());

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
