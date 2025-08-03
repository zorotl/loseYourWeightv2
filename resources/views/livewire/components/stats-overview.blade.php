<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * An empty method that listens for the 'weight-saved' event.
     * Its mere existence makes Livewire re-render the component.
     */
    #[On('weight-saved')]
    public function refresh(): void
    {
        // Intentionally left blank.
    }
}; ?>

{{-- Grid updated to handle up to 6 items gracefully --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-3">   
    {{-- NEW Stat Card for Maintenance Calories (TDEE) --}}
    <div class="relative flex flex-col gap-2 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <div class="text-sm font-medium text-zinc-500">Erhaltungsbedarf</div>
        <div class="flex items-baseline gap-2">
            <span class="text-4xl font-bold tracking-tight">{{ round(auth()->user()->tdee) }}</span>
            <span class="text-lg font-medium text-zinc-500">kcal</span>
        </div>
        <p class="text-xs text-zinc-500">Kalorien, um dein Gewicht zu halten.</p>
    </div>

    {{-- Stat Card for Target Calories --}}
    <div class="relative flex flex-col gap-2 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <div class="text-sm font-medium text-zinc-500">Dein tägliches Kalorienziel</div>
        <div class="flex items-baseline gap-2">
            <span class="text-4xl font-bold tracking-tight">{{ auth()->user()->target_calories }}</span>
            <span class="text-lg font-medium text-zinc-500">kcal</span>
        </div>
        <p class="text-xs text-zinc-500">Spare tägliche {{ auth()->user()->daily_deficit }} kcal um dein Ziel zu erreichen.</p>
    </div>

    {{-- NEW Stat Card for Daily Deficit --}}
    <div class="relative flex flex-col gap-2 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <div class="text-sm font-medium text-zinc-500">Geplantes Defizit</div>
        <div class="flex items-baseline gap-2">
            <span class="text-4xl font-bold tracking-tight">{{ auth()->user()->daily_deficit }}</span>
            <span class="text-lg font-medium text-zinc-500">kcal</span>
        </div>
        <p class="text-xs text-zinc-500">{{ auth()->user()->deficit_feedback }}</p>
    </div>

    {{-- Stat Card for Current Weight --}}
    <div class="relative flex flex-col gap-2 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <div class="text-sm font-medium text-zinc-500">Aktuelles Gewicht</div>
        @if($weight = auth()->user()->current_weight_kg)
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold tracking-tight">{{ $weight }}</span>
                <span class="text-lg font-medium text-zinc-500">kg</span>
            </div>
            <p class="text-xs text-zinc-500">
                Gewogen am: {{ auth()->user()->weightHistories()->first()->weighed_on->format('d.m.Y') }}
            </p>
        @else
            <p class="text-zinc-500">Noch kein Gewicht eingetragen.</p>
        @endif
    </div>
    
    {{-- Stat Card for Weight Goal --}}
    <div class="relative flex flex-col gap-2 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <div class="text-sm font-medium text-zinc-500">Dein Gewichtsziel</div>
        <div class="flex items-baseline gap-2">
            <span class="text-4xl font-bold tracking-tight">{{ auth()->user()->target_weight_kg }}</span>
            <span class="text-lg font-medium text-zinc-500">kg</span>
        </div>
        @if($timeRemaining = auth()->user()->goal_time_remaining)
            <p class="text-xs text-zinc-500">
                Geplant zu erreichen: {{ $timeRemaining }}
            </p>
        @endif
    </div>

    {{-- Stat Card for BMI --}}
    <div class="relative flex flex-col gap-2 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <div class="text-sm font-medium text-zinc-500">Body-Mass-Index (BMI)</div>
        <div class="flex items-baseline gap-2">
            <span class="text-4xl font-bold tracking-tight">{{ auth()->user()->bmi }}</span>
        </div>
        <p class="text-xs text-zinc-500">Dein Ziel-BMI: {{ auth()->user()->target_bmi }}</p>
    </div>
</div>