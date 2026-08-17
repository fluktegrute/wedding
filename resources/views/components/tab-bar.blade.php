<nav class="fixed inset-x-0 bottom-0 z-20">
    <div class="relative mx-auto flex max-w-md items-center justify-around border-t border-zinc-200 bg-white/95 px-2 pt-2 shadow-[0_-4px_16px_-8px_rgba(75,75,75,0.15)] backdrop-blur" style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom))">
        <a
            href="{{ route('dashboard') }}"
            wire:navigate
            class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-1.5 {{ request()->routeIs('dashboard') ? 'text-blossom' : 'text-zinc-400' }}"
        >
            <flux:icon icon="home" :variant="request()->routeIs('dashboard') ? 'solid' : 'outline'" class="size-5" />
            <span class="text-[0.625rem] font-medium">{{ __('Accueil') }}</span>
        </a>

        <div class="w-16 shrink-0" aria-hidden="true"></div>

        <a
            href="{{ route('photos.index') }}"
            wire:navigate
            class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-1.5 {{ request()->routeIs('photos.*') ? 'text-blossom' : 'text-zinc-400' }}"
        >
            <flux:icon icon="photo" :variant="request()->routeIs('photos.*') ? 'solid' : 'outline'" class="size-5" />
            <span class="text-[0.625rem] font-medium">{{ __('Galerie') }}</span>
        </a>
    </div>

    <a
        href="{{ route('photos.create') }}"
        wire:navigate
        class="absolute -top-6 left-1/2 flex -translate-x-1/2 items-center gap-1.5 rounded-full bg-blossom px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blossom/40"
    >
        <flux:icon.camera class="size-4" />
        {{ __('Photo') }}
    </a>
</nav>
