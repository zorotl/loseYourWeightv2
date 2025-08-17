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
        $this->redirect(route('log.index', ['date' => $newDate]), navigate: true);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    {{-- Date Navigation --}}
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
        
        <div class="relative flex cursor-pointer items-center" @click="$refs.dateInput.click()">
             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-gray-400">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </div>

            <input 
                x-ref="dateInput" 
                type="text" 
                readonly
                class="block w-full cursor-pointer rounded-md border-gray-300 pl-10 text-center font-semibold text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                value="{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d. F Y') }}"
            >
        </div>
        
        <flux:button wire:click="nextDay" variant="outline" :disabled="\Carbon\Carbon::parse($date)->isToday()">Nächster Tag &rarr;</flux:button>
    </div>
    
    {{-- The Calorie Tracker Component --}}
    <div class="mt-4">
        <livewire:components.calorie-tracker :date="$date" wire:key="$date" />
    </div>
</div>