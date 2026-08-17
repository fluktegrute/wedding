<?php

namespace App\Livewire\Photos;

use App\Livewire\Concerns\SummarizesReactions;
use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Galerie')]
class Index extends Component
{
    use SummarizesReactions;
    use WithPagination;

    #[Url]
    public ?int $tag = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Photo::class);
    }

    public function render(): View
    {
        $photos = Photo::query()
            ->with('user')
            ->withCount(['comments', 'reactions'])
            ->when($this->tag, fn ($query) => $query->whereRelation('tags', 'tags.id', $this->tag))
            ->latest()
            ->paginate(12);

        return view('livewire.photos.index', [
            'photos' => $photos,
            'reactionCounts' => $this->reactionCountsByPhoto($photos->pluck('id')),
            'availableTags' => Tag::query()->orderBy('name')->get(),
        ]);
    }
}
