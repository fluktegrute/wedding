@props(['active'])

<div class="mb-6 flex flex-wrap gap-2 border-b border-zinc-900/10 pb-4">
    <flux:button
        size="sm"
        variant="{{ $active === 'photos' ? 'primary' : 'subtle' }}"
        class="rounded-full"
        :href="route('admin.photos')"
        wire:navigate
    >
        {{ __('Photos') }}
    </flux:button>

    <flux:button
        size="sm"
        variant="{{ $active === 'tags' ? 'primary' : 'subtle' }}"
        class="rounded-full"
        :href="route('admin.tags')"
        wire:navigate
    >
        {{ __('Tags') }}
    </flux:button>

    <flux:button
        size="sm"
        variant="{{ $active === 'qr-code' ? 'primary' : 'subtle' }}"
        class="rounded-full"
        :href="route('admin.qr-code')"
        wire:navigate
    >
        {{ __('QR Code') }}
    </flux:button>
</div>
