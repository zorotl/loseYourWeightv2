<?php

use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.app')]
class extends Component
{
    public string $date;

    public function mount($date = null): void
    {
        $this->date = $date ? Carbon::parse($date)->toDateString() : today()->toDateString();
    }

    // NEU: Diese Methode wird aufgerufen, wenn wire:model="date" sich ändert
    public function updatedDate($value): void
    {
        $this->jumpToDate($value);
    }

    public function previousDay(): void
    {
        $newDate = Carbon::parse($this->date)->subDay()->toDateString();
        $this->redirect(route('log.index', ['date' => $newDate]), navigate: true);
    }

    public function nextDay(): void
    {
        $newDate = Carbon::parse($this->date)->addDay()->toDateString();
        $this->redirect(route('log.index', ['date' => $newDate]), navigate: true);
    }

    public function jumpToDate(string $newDate): void
    {
        $this->redirect(route('log.index', ['date' => $newDate]), navigate: true);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">    
    {{-- Date Navigation --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
        <div 
            class="flex items-center justify-between"
            x-data
            x-init="
                $nextTick(() => {
                    flatpickr($refs.dateInput, {
                        dateFormat: 'Y-m-d',
                        defaultDate: '{{ $date }}',
                        locale: German,
                        onChange: (selectedDates, dateStr, instance) => {
                            $wire.jumpToDate(dateStr);
                        }
                    });
                })
            "
        >
            <flux:button wire:click="previousDay" variant="outline">&larr; Vorheriger Tag</flux:button>
            
            <div class="w-64">
                <x-date-picker-input wire:model.live="date" />
            </div>
            
            <flux:button wire:click="nextDay" variant="outline" :disabled="\Carbon\Carbon::parse($date)->isToday()">Nächster Tag &rarr;</flux:button>
        </div>
    </div>

    {{-- Weekly Overview Component --}}
    <div>
        <x-weekly-overview />
    </div>
    
    {{-- The Calorie Tracker Component --}}
    <div>
        <livewire:components.calorie-tracker :date="$date" wire:key="$date" />
    </div>
</div>