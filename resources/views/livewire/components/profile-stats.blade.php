<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Listens for the 'weight-saved' event to trigger a re-render.
     */
    #[On('weight-saved')]
    public function refresh(): void
    {
        // Intentionally left blank. The component will re-render itself.
    }
}; ?>

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    {{-- Current Weight --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
        <div class="text-sm font-medium text-zinc-500">Aktuelles Gewicht</div>
        @if($weight = auth()->user()->current_weight_kg)
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight">{{ $weight }}</span>
                <span class="text-base font-medium text-zinc-500">kg</span>
            </div>
        @else
            <p class="mt-2 text-zinc-500">Noch keine Daten</p>
        @endif
    </div>
    {{-- Current BMI --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
        <div class="text-sm font-medium text-zinc-500">Aktueller BMI</div>
         @if(auth()->user()->bmi > 0)
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight">{{ auth()->user()->bmi }}</span>
            </div>
        @else
            <p class="mt-2 text-zinc-500">Noch keine Daten</p>
        @endif
    </div>
</div>