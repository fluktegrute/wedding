<section class="w-full">
    <flux:heading size="xl" class="font-display text-4xl">{{ __('Galerie de l’Amour') }}</flux:heading>
    <flux:subheading class="mb-6">{{ __('Toutes les photos du mariage de Sophie & Julien, partagées par les invités.') }}</flux:subheading>

    @if ($availableTags->isNotEmpty())
        <div class="mb-6 flex flex-wrap gap-2">
            <flux:button
                size="sm"
                variant="{{ $tag === null ? 'primary' : 'outline' }}"
                class="rounded-full"
                wire:click="$set('tag', null)"
            >
                {{ __('Toutes') }}
            </flux:button>

            @foreach ($availableTags as $availableTag)
                <flux:button
                    size="sm"
                    variant="{{ $tag === $availableTag->id ? 'primary' : 'outline' }}"
                    class="rounded-full"
                    wire:click="$set('tag', {{ $availableTag->id }})"
                >
                    {{ $availableTag->name }}
                </flux:button>
            @endforeach
        </div>
    @endif

    @if ($photos->isEmpty())
        <flux:callout icon="photo">
            <flux:callout.heading>{{ __('Aucune photo pour le moment') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Soyez le premier à partager un souvenir du mariage de Sophie & Julien.') }}</flux:callout.text>
            <x-slot name="actions">
                <flux:button :href="route('photos.create')" wire:navigate>{{ __('Déposer une photo') }}</flux:button>
            </x-slot>
        </flux:callout>
    @else
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($photos as $photo)
                <x-photo-card :photo="$photo" :reaction-counts="$reactionCounts->get($photo->id)" wire:key="photo-{{ $photo->id }}" />
            @endforeach
        </div>

        <div class="mt-6">
            {{ $photos->links() }}
        </div>
    @endif
</section>
