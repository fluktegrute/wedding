<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-cream text-ink">
        <div class="floral-backdrop" aria-hidden="true"></div>

        <flux:header container sticky class="border-b border-zinc-200 bg-zinc-50">
            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:spacer />

            <x-user-menu />
        </flux:header>

        <flux:main container class="pb-28 lg:pb-8">
            {{ $slot }}
        </flux:main>

        <x-tab-bar />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
