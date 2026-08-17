<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Bienvenue') }} - {{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-cream text-ink antialiased">
        <div class="flex min-h-screen flex-col">
            <header class="mx-auto w-full max-w-5xl px-6 py-8">
                <nav class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 font-medium" wire:navigate>
                        <span class="flex aspect-square size-8 items-center justify-center rounded-md bg-blossom text-ink">
                            <x-app-logo-icon class="size-5 fill-current" />
                        </span>
                        <span>{{ config('app.name', 'Laravel') }}</span>
                    </a>

                    <div class="flex items-center gap-4 text-sm">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-md bg-blossom px-4 py-2 font-medium text-ink hover:bg-blossom/80" wire:navigate>
                                {{ __('Tableau de bord') }}
                            </a>
                        @else
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="font-medium text-ink hover:text-blossom" wire:navigate>
                                    {{ __('Se connecter') }}
                                </a>
                            @endif

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="rounded-md bg-blossom px-4 py-2 font-medium text-ink hover:bg-blossom/80" wire:navigate>
                                    {{ __('Créer un compte') }}
                                </a>
                            @endif
                        @endauth
                    </div>
                </nav>
            </header>

            <main class="mx-auto flex w-full max-w-5xl flex-1 flex-col items-center justify-center px-6 py-16 text-center">
                <span class="mb-4 rounded-full bg-sage px-4 py-1 text-sm font-medium text-ink">
                    {{ __('Le mariage de Sophie & Julien') }}
                </span>

                <p class="font-display text-xl text-blossom sm:text-2xl">
                    {{ __('22 août 2026') }}
                </p>

                <h1 class="font-display mt-2 max-w-2xl text-4xl text-ink sm:text-5xl">
                    {{ __('Tous nos souvenirs, réunis en un seul endroit') }}
                </h1>

                <p class="mt-6 max-w-xl text-lg text-zinc-600">
                    {{ __('Coucou à tous ! Les meilleurs souvenirs de cette journée, c\'est vous qui les avez, planqués dans vos téléphones. Alors partagez-les ici : vos photos, vos petits mots, vos réactions sur celles des autres. On a hâte de tout revoir avec vos yeux à vous. Merci d\'être des nôtres, on vous aime fort !') }}
                </p>

                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md bg-blossom px-6 py-3 font-medium text-ink hover:bg-blossom/80" wire:navigate>
                            {{ __('Voir la galerie') }}
                        </a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-md bg-blossom px-6 py-3 font-medium text-ink hover:bg-blossom/80" wire:navigate>
                                {{ __('Déposer mes photos') }}
                            </a>
                        @endif

                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="rounded-md border border-zinc-200 bg-white px-6 py-3 font-medium text-ink hover:border-blossom" wire:navigate>
                                {{ __('J\'ai déjà un compte') }}
                            </a>
                        @endif
                    @endauth
                </div>

                <div class="mt-20 grid w-full gap-4 sm:grid-cols-3">
                    <div class="rounded-xl bg-white p-6 text-start shadow-xs">
                        <span class="flex size-10 items-center justify-center rounded-full bg-sage text-ink">📷</span>
                        <h2 class="mt-4 font-medium text-ink">{{ __('Partagez') }}</h2>
                        <p class="mt-1 text-sm text-zinc-600">{{ __('Déposez vos photos et ajoutez un petit mot si vous le souhaitez.') }}</p>
                    </div>

                    <div class="rounded-xl bg-white p-6 text-start shadow-xs">
                        <span class="flex size-10 items-center justify-center rounded-full bg-periwinkle text-ink">💬</span>
                        <h2 class="mt-4 font-medium text-ink">{{ __('Réagissez') }}</h2>
                        <p class="mt-1 text-sm text-zinc-600">{{ __('Commentez et réagissez avec des emojis aux photos des autres invités.') }}</p>
                    </div>

                    <div class="rounded-xl bg-white p-6 text-start shadow-xs">
                        <span class="flex size-10 items-center justify-center rounded-full bg-honey text-ink">✨</span>
                        <h2 class="mt-4 font-medium text-ink">{{ __('Revivez') }}</h2>
                        <p class="mt-1 text-sm text-zinc-600">{{ __('Sophie et Julien mettent en avant leurs photos préférées pour toute la famille.') }}</p>
                    </div>
                </div>
            </main>

            <footer class="mx-auto w-full max-w-5xl px-6 py-8 text-center text-sm text-zinc-500">
                {{ config('app.name', 'Laravel') }} &middot; {{ now()->year }}
            </footer>
        </div>
    </body>
</html>
