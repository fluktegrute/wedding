<?php

namespace App\Livewire\Concerns;

use App\Models\Reaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait SummarizesReactions
{
    /**
     * Reaction counts per emoji, grouped by photo id, in a single query.
     *
     * @param  iterable<int, int>  $photoIds
     * @return Collection<int, Collection<string, int>>
     */
    protected function reactionCountsByPhoto(iterable $photoIds): Collection
    {
        return Reaction::query()
            ->select('photo_id', 'emoji', DB::raw('count(*) as aggregate'))
            ->whereIn('photo_id', $photoIds)
            ->groupBy('photo_id', 'emoji')
            ->get()
            ->groupBy(fn (Reaction $reaction): int => (int) $reaction->photo_id)
            ->map(fn (Collection $rows): Collection => $rows->mapWithKeys(
                fn (Reaction $reaction): array => [(string) $reaction->emoji => (int) $reaction->getAttribute('aggregate')]
            ));
    }
}
