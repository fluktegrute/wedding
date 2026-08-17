<section class="w-full">
    <flux:heading size="xl" class="font-display text-4xl">{{ __('Espace de Sophie & Julien') }}</flux:heading>
    <flux:subheading class="mb-6">{{ __('Mettez en avant vos photos préférées et sélectionnez celles à exporter vers Google Drive.') }}</flux:subheading>

    <x-admin-nav active="photos" />

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach (['all' => __('Toutes'), 'featured' => __('En avant'), 'pending' => __('À synchroniser'), 'synced' => __('Synchronisées')] as $value => $label)
            <flux:button
                size="sm"
                variant="{{ $filter === $value ? 'primary' : 'outline' }}"
                class="rounded-full"
                wire:click="$set('filter', '{{ $value }}')"
            >
                {{ $label }}
            </flux:button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_18rem]">
        <div>
            @if ($photos->isEmpty())
                <flux:callout icon="photo">
                    <flux:callout.heading>{{ __('Aucune photo ne correspond à ce filtre') }}</flux:callout.heading>
                </flux:callout>
            @else
                <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                    @foreach ($photos as $photo)
                        <div wire:key="photo-{{ $photo->id }}" class="group relative overflow-hidden rounded-2xl bg-zinc-100 shadow-sm ring-1 ring-zinc-900/5">
                            <a href="{{ route('photos.show', $photo) }}" wire:navigate class="block">
                                <img
                                    src="{{ $photo->thumbnail_url }}"
                                    alt="{{ $photo->original_filename }}"
                                    loading="lazy"
                                    class="aspect-square size-full object-cover"
                                >
                            </a>

                            @if ($photo->is_featured)
                                <flux:icon.star variant="solid" class="absolute top-2 start-2 size-5 text-honey drop-shadow" />
                            @endif

                            <div class="absolute bottom-2 end-2 flex items-center gap-1 rounded-full bg-ink/70 p-1 backdrop-blur">
                                <flux:tooltip :content="$photo->is_featured ? __('Retirer la mise en avant') : __('Mettre en avant')">
                                    <button
                                        type="button"
                                        wire:click="toggleFeatured({{ $photo->id }})"
                                        class="flex size-7 items-center justify-center rounded-full {{ $photo->is_featured ? 'bg-honey text-ink' : 'text-white hover:bg-white/20' }}"
                                    >
                                        <flux:icon.star variant="{{ $photo->is_featured ? 'solid' : 'outline' }}" class="size-4" />
                                    </button>
                                </flux:tooltip>

                                <flux:tooltip :content="$photo->is_synced_to_gdrive ? __('Retirer de Google Drive') : __('Exporter vers Google Drive')">
                                    <button
                                        type="button"
                                        wire:click="toggleSync({{ $photo->id }})"
                                        class="flex size-7 items-center justify-center rounded-full {{ $photo->is_synced_to_gdrive ? 'bg-blossom text-white' : 'text-white hover:bg-white/20' }}"
                                    >
                                        <flux:icon.cloud-arrow-up variant="{{ $photo->is_synced_to_gdrive ? 'solid' : 'outline' }}" class="size-4" />
                                    </button>
                                </flux:tooltip>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $photos->links() }}
                </div>
            @endif
        </div>

        <aside class="hidden lg:block">
            <flux:card class="rounded-3xl shadow-sm ring-1 ring-zinc-900/5">
                <flux:heading size="sm" class="mb-1">{{ __('Mises en avant') }}</flux:heading>
                <flux:text size="sm" class="mb-4 text-zinc-500">{{ __('Ce que les invités voient en premier.') }}</flux:text>

                @if ($featuredPhotos->isEmpty())
                    <flux:text size="sm" class="text-zinc-400">{{ __('Aucune photo mise en avant pour le moment.') }}</flux:text>
                @else
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($featuredPhotos as $photo)
                            <a href="{{ route('photos.show', $photo) }}" wire:navigate wire:key="featured-{{ $photo->id }}" class="block overflow-hidden rounded-xl">
                                <img src="{{ $photo->thumbnail_url }}" alt="{{ $photo->original_filename }}" loading="lazy" class="aspect-square size-full object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        </aside>
    </div>
</section>
