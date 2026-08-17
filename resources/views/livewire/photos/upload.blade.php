<section class="mx-auto w-full max-w-lg">
    <flux:heading size="xl" class="font-display text-4xl">{{ __('Déposer une photo') }}</flux:heading>
    <flux:subheading class="mb-6">{{ __('Partagez vos photos du mariage de Sophie & Julien avec les autres invités.') }}</flux:subheading>

    <flux:card class="rounded-3xl shadow-sm ring-1 ring-zinc-900/5">
        <form wire:submit="save" class="space-y-6">
            <div>
                <label
                    for="photo-upload-input"
                    class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-zinc-300 bg-zinc-50 px-6 py-10 text-center transition-colors hover:border-blossom hover:bg-blossom/5"
                >
                    @if ($image && $image->isPreviewable())
                        <img src="{{ $image->temporaryUrl() }}" class="max-h-64 rounded-xl object-cover" alt="{{ __('Aperçu de la photo') }}">
                    @else
                        <span class="text-3xl">📷</span>
                        <flux:text class="font-medium">{{ __('Appuyez pour choisir une photo') }}</flux:text>
                        <flux:text size="sm" class="text-zinc-400">{{ __('JPG, PNG, HEIC…') }}</flux:text>
                    @endif
                </label>

                <input id="photo-upload-input" type="file" wire:model="image" accept="image/*" class="sr-only">

                <flux:error name="image" class="mt-2" />
            </div>

            <flux:textarea wire:model="description" :label="__('Légende (optionnelle)')" rows="3" />

            @if ($availableTags->isNotEmpty())
                <flux:checkbox.group wire:model="tags" variant="pills" :label="__('Tags (optionnel)')">
                    @foreach ($availableTags as $tag)
                        <flux:checkbox :value="$tag->id" :label="$tag->name" />
                    @endforeach
                </flux:checkbox.group>
            @endif

            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary" class="rounded-full" wire:loading.attr="disabled" wire:target="image,save">
                    {{ __('Partager ma photo') }}
                </flux:button>

                <span wire:loading wire:target="image">{{ __('Envoi de la photo…') }}</span>
            </div>
        </form>
    </flux:card>
</section>
