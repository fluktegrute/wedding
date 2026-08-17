<section class="w-full">
    <div class="rounded-3xl bg-gradient-to-br from-blossom/20 to-honey/20 px-6 py-10 text-center">
        <flux:heading size="xl" class="font-display text-3xl sm:text-4xl">{{ __('Merci de partager vos souvenirs') }}</flux:heading>
        <flux:subheading class="mx-auto mt-2 max-w-md">
            {{ __('Immortalisez le mariage de Sophie & Julien en ajoutant vos photos.') }}
        </flux:subheading>

        <flux:button :href="route('photos.create')" wire:navigate variant="primary" icon="arrow-up-tray" class="mt-6 rounded-full">
            {{ __('Ajouter une photo') }}
        </flux:button>
    </div>

    @if ($featuredPhotos->isEmpty() && $recentPhotos->isEmpty())
        <flux:callout icon="photo" class="mt-8">
            <flux:callout.heading>{{ __('Aucune photo pour le moment') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Soyez le premier à partager un souvenir du mariage de Sophie & Julien.') }}</flux:callout.text>
        </flux:callout>
    @else
        @if ($featuredPhotos->isNotEmpty())
            <div class="mt-8">
                <div class="mb-4 flex items-center gap-2">
                    <flux:icon.star variant="solid" class="size-5 text-honey" />
                    <flux:heading size="lg" class="font-display text-2xl">{{ __('Mises en avant') }}</flux:heading>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                    @foreach ($featuredPhotos as $photo)
                        <x-photo-card :photo="$photo" :reaction-counts="$reactionCounts->get($photo->id)" size="featured" wire:key="featured-{{ $photo->id }}" />
                    @endforeach
                </div>
            </div>
        @endif

        @if ($recentPhotos->isNotEmpty())
            <div class="mt-10">
                <flux:heading size="lg" class="font-display mb-4 text-2xl">{{ __('Photos récentes') }}</flux:heading>

                <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($recentPhotos as $photo)
                        <x-photo-card :photo="$photo" :reaction-counts="$reactionCounts->get($photo->id)" wire:key="recent-{{ $photo->id }}" />
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</section>
