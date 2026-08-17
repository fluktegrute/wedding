<?php

namespace App\Livewire;

use App\Livewire\Concerns\SummarizesReactions;
use App\Models\Photo;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Accueil')]
class Home extends Component
{
    use SummarizesReactions;

    public function render(): View
    {
        $featuredPhotos = Photo::query()
            ->with('user')
            ->withCount(['comments', 'reactions'])
            ->where('is_featured', true)
            ->latest()
            ->limit(6)
            ->get();

        $recentPhotos = Photo::query()
            ->with('user')
            ->withCount(['comments', 'reactions'])
            ->latest()
            ->limit(8)
            ->get();

        $photoIds = $featuredPhotos->pluck('id')
            ->concat($recentPhotos->pluck('id'))
            ->unique();

        return view('livewire.home', [
            'featuredPhotos' => $featuredPhotos,
            'recentPhotos' => $recentPhotos,
            'reactionCounts' => $this->reactionCountsByPhoto($photoIds),
        ]);
    }
}
