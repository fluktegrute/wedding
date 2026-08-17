<section class="w-full max-w-2xl">
    <flux:heading size="xl" class="font-display text-4xl">{{ __('Tags') }}</flux:heading>
    <flux:subheading class="mb-6">{{ __('Gérez les tags proposés aux invités pour classer leurs photos (cérémonie, cocktail, soirée…).') }}</flux:subheading>

    <x-admin-nav active="tags" />

    <flux:card class="mb-6 rounded-3xl shadow-sm ring-1 ring-zinc-900/5">
        <form wire:submit="{{ $editing ? 'update' : 'create' }}" class="flex items-start gap-3">
            <flux:input wire:model="name" :placeholder="__('Nom du tag')" class="flex-1" />

            <flux:button type="submit" variant="primary" class="rounded-full">
                {{ $editing ? __('Renommer') : __('Ajouter') }}
            </flux:button>

            @if ($editing)
                <flux:button type="button" variant="subtle" class="rounded-full" wire:click="cancelEdit">
                    {{ __('Annuler') }}
                </flux:button>
            @endif
        </form>
    </flux:card>

    @if ($tags->isEmpty())
        <flux:callout icon="tag">
            <flux:callout.heading>{{ __('Aucun tag pour le moment') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Créez votre premier tag ci-dessus pour permettre aux invités de classer leurs photos.') }}</flux:callout.text>
        </flux:callout>
    @else
        <div class="flex flex-col divide-y divide-zinc-900/5 rounded-3xl bg-white shadow-sm ring-1 ring-zinc-900/5">
            @foreach ($tags as $tag)
                <div class="flex items-center justify-between gap-3 px-4 py-3" wire:key="tag-{{ $tag->id }}">
                    <div class="flex items-center gap-2">
                        <flux:text class="font-medium">{{ $tag->name }}</flux:text>
                        <flux:badge size="sm" class="rounded-full">
                            {{ trans_choice(':count photo|:count photos', $tag->photos_count, ['count' => $tag->photos_count]) }}
                        </flux:badge>
                    </div>

                    <div class="flex items-center gap-1">
                        <flux:button size="sm" variant="subtle" icon="pencil" inset wire:click="edit({{ $tag->id }})" />
                        <flux:button
                            size="sm"
                            variant="subtle"
                            icon="trash"
                            inset
                            wire:click="delete({{ $tag->id }})"
                            wire:confirm="{{ __('Supprimer ce tag ? Il sera retiré de toutes les photos.') }}"
                        />
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
