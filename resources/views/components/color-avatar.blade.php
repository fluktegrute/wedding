@props([
    'name' => '',
    'id' => null,
    'size' => 'sm',
])

@php
    // Literal class names so Tailwind's scanner picks them up in this file's source.
    $palette = ['bg-blossom', 'bg-honey', 'bg-periwinkle', 'bg-sage', 'bg-willow'];
    $background = $palette[($id ?? crc32($name)) % count($palette)];

    $initials = \Illuminate\Support\Str::of($name)
        ->trim()
        ->explode(' ')
        ->filter()
        ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->take(2)
        ->implode('');

    $sizeClasses = match ($size) {
        'xs' => 'size-6 text-[0.625rem]',
        'md' => 'size-10 text-sm',
        'lg' => 'size-12 text-base',
        default => 'size-8 text-xs',
    };
@endphp

<span {{ $attributes->class(["inline-flex shrink-0 items-center justify-center rounded-full font-semibold text-ink", $background, $sizeClasses]) }}>
    {{ $initials }}
</span>
