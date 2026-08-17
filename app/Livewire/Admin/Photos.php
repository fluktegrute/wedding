<?php

namespace App\Livewire\Admin;

use App\Jobs\SyncPhotoWithGoogleDriveJob;
use App\Models\Photo;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Administration — Photos')]
class Photos extends Component
{
    use WithPagination;

    /** @var 'all'|'featured'|'synced'|'pending' */
    #[Url]
    public string $filter = 'all';

    public function toggleFeatured(Photo $photo): void
    {
        $this->authorize('feature', $photo);

        $photo->is_featured = ! $photo->is_featured;
        $photo->save();
    }

    public function toggleSync(Photo $photo): void
    {
        $this->authorize('syncToGdrive', $photo);

        $wasSynced = $photo->is_synced_to_gdrive;

        SyncPhotoWithGoogleDriveJob::dispatch($photo, export: ! $wasSynced);

        Flux::toast(variant: 'success', text: $wasSynced
            ? __('Retrait de Google Drive lancé.')
            : __('Synchronisation avec Google Drive lancée.'));
    }

    public function render(): View
    {
        $photos = Photo::query()
            ->with('user')
            ->when($this->filter === 'featured', fn ($query) => $query->where('is_featured', true))
            ->when($this->filter === 'synced', fn ($query) => $query->where('is_synced_to_gdrive', true))
            ->when($this->filter === 'pending', fn ($query) => $query->where('is_synced_to_gdrive', false))
            ->latest()
            ->paginate(12);

        $featuredPhotos = Photo::query()
            ->where('is_featured', true)
            ->latest()
            ->limit(6)
            ->get();

        return view('livewire.admin.photos', [
            'photos' => $photos,
            'featuredPhotos' => $featuredPhotos,
        ]);
    }
}
