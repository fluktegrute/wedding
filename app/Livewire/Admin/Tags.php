<?php

namespace App\Livewire\Admin;

use App\Models\Tag;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Administration — Tags')]
class Tags extends Component
{
    public string $name = '';

    public ?Tag $editing = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Tag::class);
    }

    /**
     * Create a new tag.
     */
    public function create(): void
    {
        $this->authorize('create', Tag::class);

        $this->validate(['name' => $this->nameRules()]);

        Tag::create(['name' => $this->name]);

        Flux::toast(variant: 'success', text: __('Tag créé.'));

        $this->reset('name');
    }

    /**
     * Start editing a tag's name.
     */
    public function edit(Tag $tag): void
    {
        $this->authorize('update', $tag);

        $this->editing = $tag;
        $this->name = $tag->name;
    }

    /**
     * Save the tag being renamed.
     */
    public function update(): void
    {
        $this->authorize('update', $this->editing);

        $this->validate(['name' => $this->nameRules()]);

        $this->editing->update(['name' => $this->name]);

        Flux::toast(variant: 'success', text: __('Tag renommé.'));

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->editing = null;
        $this->reset('name');
        $this->resetValidation();
    }

    /**
     * Delete a tag, removing it from every photo it was applied to.
     */
    public function delete(Tag $tag): void
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        Flux::toast(variant: 'success', text: __('Tag supprimé.'));
    }

    /**
     * @return array<int, mixed>
     */
    private function nameRules(): array
    {
        return ['required', 'string', 'max:50', Rule::unique('tags', 'name')->ignore($this->editing?->id)];
    }

    public function render(): View
    {
        return view('livewire.admin.tags', [
            'tags' => Tag::withCount('photos')->orderBy('name')->get(),
        ]);
    }
}
