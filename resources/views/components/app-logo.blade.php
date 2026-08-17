<flux:brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
    <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-full bg-blossom text-white">
        <x-app-logo-icon class="size-4.5 fill-current" />
    </x-slot>
</flux:brand>
