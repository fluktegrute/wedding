<?php

namespace App\Livewire\Photos;

use App\Models\Comment;
use App\Models\Photo;
use App\Models\Reaction;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Photo')]
class Show extends Component
{
    /** @var list<string> */
    public const array EMOJIS = ['❤️', '😍', '😂', '👏', '🎉', '👍'];

    public Photo $photo;

    #[Validate('required|string|max:1000')]
    public string $newComment = '';

    public function mount(Photo $photo): void
    {
        $this->authorize('view', $photo);

        $this->photo = $photo->load('user', 'tags');
    }

    /**
     * Delete the photo, its comments and reactions (author or admin only).
     */
    public function deletePhoto(): void
    {
        $this->authorize('delete', $this->photo);

        $this->photo->delete();

        Flux::toast(variant: 'success', text: __('Photo supprimée.'));

        $this->redirect(route('photos.index'), navigate: true);
    }

    /**
     * Delete a comment (its author or an admin only).
     */
    public function deleteComment(Comment $comment): void
    {
        $this->authorize('delete', $comment);

        $comment->delete();
    }

    /**
     * Add a comment to the photo on behalf of the authenticated guest.
     */
    public function addComment(): void
    {
        $this->authorize('create', Comment::class);

        $this->validate();

        $comment = new Comment(['body' => $this->newComment]);
        $comment->photo()->associate($this->photo);
        $comment->user()->associate(Auth::user());
        $comment->save();

        $this->reset('newComment');
    }

    /**
     * Set, change, or remove the authenticated guest's reaction to the photo.
     */
    public function toggleReaction(string $emoji): void
    {
        if (! in_array($emoji, self::EMOJIS, true)) {
            return;
        }

        $reaction = $this->photo->reactions()->where('user_id', Auth::id())->first();

        if ($reaction?->emoji === $emoji) {
            $this->authorize('delete', $reaction);

            $reaction->delete();

            return;
        }

        if ($reaction) {
            $this->authorize('update', $reaction);

            $reaction->update(['emoji' => $emoji]);

            return;
        }

        $this->authorize('create', Reaction::class);

        $reaction = new Reaction(['emoji' => $emoji]);
        $reaction->photo()->associate($this->photo);
        $reaction->user()->associate(Auth::user());
        $reaction->save();
    }

    /**
     * Reactions grouped by emoji, with the count and the names of the reactors.
     *
     * @param  EloquentCollection<int, Reaction>  $reactions
     * @return Collection<int|string, array{emoji: string, count: int<0, max>, names: Collection<int|string, string>}>
     */
    protected function reactionsSummary(EloquentCollection $reactions): Collection
    {
        return $reactions
            ->groupBy(fn (Reaction $reaction): string => $reaction->emoji)
            ->map(fn (Collection $reactions, string $emoji): array => [
                'emoji' => $emoji,
                'count' => $reactions->count(),
                'names' => $reactions->map(fn (Reaction $reaction): string => $reaction->user->name),
            ]);
    }

    public function render(): View
    {
        $reactions = $this->photo->reactions()->with('user')->get();

        return view('livewire.photos.show', [
            'comments' => $this->photo->comments()->with('user')->oldest()->get(),
            'reactionsSummary' => $this->reactionsSummary($reactions),
            'userReactionEmoji' => $reactions->firstWhere('user_id', Auth::id())?->emoji,
        ]);
    }
}
