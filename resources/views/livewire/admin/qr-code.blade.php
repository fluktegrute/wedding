<section class="w-full max-w-md">
    <flux:heading size="xl" class="font-display text-4xl">{{ __('QR Code') }}</flux:heading>
    <flux:subheading class="mb-6">{{ __('À afficher ou imprimer pour que les invités accèdent directement au site.') }}</flux:subheading>

    <x-admin-nav active="qr-code" />

    <flux:card class="flex flex-col items-center gap-6 rounded-3xl p-8 text-center shadow-sm ring-1 ring-zinc-900/5">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-zinc-900/5">
            {!! $svg !!}
        </div>

        <flux:text class="text-zinc-500">
            {{ __('Ce QR code pointe automatiquement vers l’adresse actuelle du site, quel que soit le nom de domaine utilisé.') }}
        </flux:text>

        <flux:input :value="$url" readonly copyable class="w-full" />

        <flux:button variant="primary" class="rounded-full" x-data="" x-on:click="window.print()">
            {{ __('Imprimer') }}
        </flux:button>
    </flux:card>
</section>
