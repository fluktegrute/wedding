@props([
    'photo',
    'reactionCounts' => null,
    'size' => 'default',
])

@php
    $reactionCounts ??= collect();
    $aspect = $size === 'featured' ? 'aspect-[4/3]' : 'aspect-[4/5]';
@endphp

<a href="{{ route('photos.show', $photo) }}" wire:navigate class="group block h-full">
    <flux:card size="sm" class="flex h-full flex-col gap-0 overflow-hidden rounded-3xl p-0! shadow-sm ring-1 ring-zinc-900/5 transition-shadow hover:shadow-md">
        <div class="relative {{ $aspect }} overflow-hidden bg-zinc-100">
            <img
                src="{{ $photo->thumbnail_url }}"
                alt="{{ $photo->original_filename }}"
                loading="lazy"
                class="size-full object-cover transition-transform duration-200 group-hover:scale-105"
            >

            @if ($photo->is_featured)
                <flux:badge icon="star" color="amber" size="sm" class="absolute top-2 start-2 shadow-sm">
                    {{ __('En avant') }}
                </flux:badge>
            @endif
        </div>

        <div class="flex flex-1 flex-col gap-2 px-3 py-3">
            <div class="flex items-center gap-2">
                <x-color-avatar :name="$photo->user->name" :id="$photo->user->id" size="xs" />
                <flux:text size="sm" class="truncate font-medium">{{ $photo->user->name }}</flux:text>
                <flux:text size="sm" class="ms-auto shrink-0 text-zinc-400">{{ $photo->created_at?->diffForHumans() }}</flux:text>
            </div>

            @if ($photo->description)
                <flux:text size="sm" class="line-clamp-2 text-zinc-500">{{ $photo->description }}</flux:text>
            @endif

            <div class="mt-auto flex items-center gap-1.5 pt-1">
                @foreach ($reactionCounts as $emoji => $count)
                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-0.5 text-xs">
                        {{ $emoji }} {{ $count }}
                    </span>
                @endforeach

                <span class="ms-auto flex items-center gap-1 text-xs text-zinc-400">
                    💬 {{ $photo->comments_count }}
                </span>
            </div>
        </div>
    </flux:card>
</a>
