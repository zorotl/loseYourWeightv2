<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    #[Rule('required|numeric|min:30|max:300')]
    public float|string $weight_kg = '';

    public function saveWeight()
    {
        $validated = $this->validate();

        auth()->user()->weightHistories()->create([
            'weight_kg' => $validated['weight_kg'],
            'weighed_on' => now(),
        ]);

        $this->weight_kg = '';
        
        // This dispatches an event to tell other components to refresh.
        // It's like screaming "I'M DONE!" across the room.
        $this->dispatch('weight-saved');
    }
}; ?>

<div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Neues Gewicht eintragen</h3>
    <form wire:submit="saveWeight" class="mt-4 space-y-4">
        <div>
            <flux:input
                wire:model="weight_kg"
                :label="__('Aktuelles Gewicht (kg)')"
                type="number"
                step="0.1"
                required
            />
        </div>
        <div class="pt-2">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Speichern') }}
            </flux:button>
        </div>
    </form>
</div>