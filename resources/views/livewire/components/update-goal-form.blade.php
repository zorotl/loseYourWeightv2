<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    #[Rule('required|numeric|min:30|max:200')]
    public float|string $target_weight_kg;

    #[Rule('required|date|after:today')]
    public string $target_date;

    public function mount(): void
    {
        $user = auth()->user();
        $this->target_weight_kg = $user->target_weight_kg ?? '';
        $this->target_date = $user->target_date?->format('Y-m-d') ?? '';
    }

    public function saveGoal(): void
    {
        $validated = $this->validate();
        
        auth()->user()->update($validated);
        
        // Dispatch an event to notify other components (like stats-overview)
        $this->dispatch('goal-updated');
        $this->dispatch('show-toast', message: 'Ziele aktualisiert.');
    }
}; ?>

<div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Ziele anpassen</h3>
    <form wire:submit="saveGoal" class="mt-4 space-y-4">
        <div>
            <flux:input 
                wire:model="target_weight_kg" 
                :label="__('Zielgewicht (kg)')" 
                type="number" 
                step="0.1" 
                required 
            />
        </div>
        <div>
            <flux:input 
                wire:model="target_date" 
                :label="__('Zieldatum')" 
                type="date" 
                required 
            />
        </div>
        <div class="pt-2">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Ziele speichern') }}
            </flux:button>
        </div>
    </form>
</div>