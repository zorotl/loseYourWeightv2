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
        // Kommt vom Kalender im Format Y-m-d
        $this->redirect(route('log.index', ['date' => $newDate]), navigate: true);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    {{-- Date Navigation --}}
    <div 
        class="flex items-center justify-between"
        x-data
        x-init="
            flatpickr($refs.dateInput, {
                dateFormat: 'Y-m-d',
                defaultDate: '{{ $date }}',
                altInput: true,
                altFormat: 'l, d. F Y',
                locale: 'de',
                onChange: (selectedDates, dateStr, instance) => {
                    $wire.jumpToDate(dateStr);
                }
            });
        " {{-- <-- Das fehlerhafte " wurde hier entfernt --}}
    >
        <flux:button wire:click="previousDay" variant="outline">&larr; Vorheriger Tag</flux:button>
        
        <input 
            x-ref="dateInput" 
            type="text" 
            class="cursor-pointer border-none bg-transparent text-center text-xl font-bold tracking-tight text-gray-900 focus:ring-0 dark:text-white"
        >
        
        <flux:button wire:click="nextDay" variant="outline" :disabled="\Carbon\Carbon::parse($date)->isToday()">Nächster Tag &rarr;</flux:button>
    </div>
    
    {{-- The Calorie Tracker Component --}}
    <div class="mt-4">
        <livewire:components.calorie-tracker :date="$date" wire:key="$date" />
    </div>
</div>