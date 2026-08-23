<?php

namespace App\Livewire\Photos;

use App\Models\Photo;
use App\Models\Tag;
use App\Services\PhotoImageProcessor;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Déposer une photo')]
class Upload extends Component
{
    use WithFileUploads;

    /** SVG et autres formats vectoriels/scriptables volontairement exclus : traités par Imagick, ils exposent
     *  à des failles de type ImageTragick (délégués MSL/URL). Seuls les formats raster réellement utilisés
     *  par l'application sont acceptés. */
    #[Validate('required|mimes:jpg,jpeg,png,webp,heic,heif|max:32768')]
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

        $variants = app(PhotoImageProcessor::class)->store($this->image);

        $photo = Auth::user()->photos()->create([
            ...$variants,
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
}
