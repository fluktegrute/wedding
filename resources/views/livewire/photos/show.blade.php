<section class="w-full max-w-3xl">
    <div class="mb-4 flex items-center justify-between">
        <flux:button :href="route('photos.index')" wire:navigate variant="subtle" icon="arrow-left">
            {{ __('Retour à la galerie') }}
        </flux:button>

        @can('delete', $photo)
            <flux:modal.trigger name="confirm-photo-deletion">
                <flux:button variant="danger" icon="trash" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-photo-deletion')">
                    {{ __('Supprimer') }}
                </flux:button>
            </flux:modal.trigger>
        @endcan
    </div>

    <flux:card class="overflow-hidden rounded-3xl p-0! shadow-sm ring-1 ring-zinc-900/5">
        <div class="relative bg-zinc-100">
            <img
                src="{{ $photo->image_url }}"
                alt="{{ $photo->original_filename }}"
                class="max-h-[32rem] w-full object-contain"
            >

            @if ($photo->is_featured)
                <flux:badge icon="star" color="amber" size="sm" class="absolute top-3 start-3 shadow-sm">
                    {{ __('En avant') }}
                </flux:badge>
            @endif

            @if ($previousPhoto)
                <flux:button
                    :href="route('photos.show', $previousPhoto)"
                    wire:navigate
                    icon="chevron-left"
                    variant="filled"
                    class="absolute! top-1/2 start-3 -translate-y-1/2 rounded-full shadow-sm"
                    :aria-label="__('Photo précédente')"
                />
            @endif

            @if ($nextPhoto)
                <flux:button
                    :href="route('photos.show', $nextPhoto)"
                    wire:navigate
                    icon="chevron-right"
                    variant="filled"
                    class="absolute! top-1/2 end-3 -translate-y-1/2 rounded-full shadow-sm"
                    :aria-label="__('Photo suivante')"
                />
            @endif
        </div>

        <div class="flex flex-col gap-4 p-6">
            <div class="flex items-center gap-3">
                <x-color-avatar :name="$photo->user->name" :id="$photo->user->id" size="md" />
                <div>
                    <flux:heading class="font-display text-lg">{{ $photo->user->name }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-400">{{ $photo->created_at?->diffForHumans() }}</flux:text>
                </div>
            </div>

            @if ($photo->description)
                <flux:text class="text-base">{{ $photo->description }}</flux:text>
            @endif

            @if ($photo->tags->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($photo->tags as $tag)
                        <flux:badge size="sm" class="rounded-full">{{ $tag->name }}</flux:badge>
                    @endforeach
                </div>
            @endif

            @if ($reactionsSummary->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($reactionsSummary as $reaction)
                        <flux:tooltip :content="$reaction['names']->join(', ')">
                            <flux:badge size="lg" class="rounded-full bg-sage/60">
                                {{ $reaction['emoji'] }} {{ $reaction['count'] }}
                            </flux:badge>
                        </flux:tooltip>
                    @endforeach
                </div>
            @endif

            <div class="flex flex-wrap gap-2 rounded-2xl bg-zinc-50 p-2">
                @foreach (\App\Livewire\Photos\Show::EMOJIS as $emoji)
                    <flux:button
                        size="sm"
                        variant="{{ $userReactionEmoji === $emoji ? 'primary' : 'subtle' }}"
                        class="rounded-full text-base"
                        wire:click="toggleReaction('{{ $emoji }}')"
                    >
                        {{ $emoji }}
                    </flux:button>
                @endforeach
            </div>
        </div>
    </flux:card>

    <div class="mt-8">
        <flux:heading size="lg" class="font-display mb-4 text-2xl">
            {{ trans_choice(':count commentaire|:count commentaires', $comments->count(), ['count' => $comments->count()]) }}
        </flux:heading>

        @if ($comments->isEmpty())
            <flux:text class="italic text-zinc-400">{{ __('Soyez le premier à commenter !') }}</flux:text>
        @else
            <div class="flex flex-col gap-4">
                @foreach ($comments as $comment)
                    <div class="flex gap-3 rounded-2xl bg-white/70 p-3" wire:key="comment-{{ $comment->id }}">
                        <x-color-avatar :name="$comment->user->name" :id="$comment->user->id" />
                        <div class="flex-1">
                            <div class="flex items-baseline gap-2">
                                <flux:heading size="sm">{{ $comment->user->name }}</flux:heading>
                                <flux:text size="sm">{{ $comment->created_at?->diffForHumans() }}</flux:text>
                            </div>
                            <flux:text>{{ $comment->body }}</flux:text>
                        </div>

                        @can('delete', $comment)
                            <flux:button
                                size="sm"
                                variant="subtle"
                                icon="trash"
                                inset
                                wire:click="deleteComment({{ $comment->id }})"
                                wire:confirm="{{ __('Supprimer ce commentaire ?') }}"
                            />
                        @endcan
                    </div>
                @endforeach
            </div>
        @endif

        <form wire:submit="addComment" class="mt-4 flex items-center gap-2">
            <flux:input wire:model="newComment" :placeholder="__('Ajouter un commentaire…')" class="flex-1" />
            <flux:button type="submit" variant="primary" class="rounded-full">{{ __('Envoyer') }}</flux:button>
        </form>
    </div>

    @can('delete', $photo)
        <flux:modal name="confirm-photo-deletion" focusable class="max-w-lg">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Supprimer cette photo ?') }}</flux:heading>

                    <flux:subheading>
                        {{ __('La photo, ses commentaires et ses réactions seront définitivement supprimés.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Annuler') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" wire:click="deletePhoto">{{ __('Supprimer') }}</flux:button>
                </div>
            </div>
        </flux:modal>
    @endcan
</section>
