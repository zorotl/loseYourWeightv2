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
        // Führe eine Navigation zur neuen URL durch, anstatt nur die Eigenschaft zu ändern
        $this->redirect(route('log.index', ['date' => $newDate]), navigate: true);
    }

    public function nextDay(): void
    {
        $newDate = Carbon::parse($this->date)->addDay()->toDateString();
        // Führe eine Navigation zur neuen URL durch
        $this->redirect(route('log.index', ['date' => $newDate]), navigate: true);
    }

    public function getFormattedDateProperty(): string
    {
        $carbonDate = Carbon::parse($this->date);
        if ($carbonDate->isToday()) return 'Heute (' . $carbonDate->translatedFormat('d. F Y') . ')';
        if ($carbonDate->isYesterday()) return 'Gestern (' . $carbonDate->translatedFormat('d. F Y') . ')';
        return $carbonDate->translatedFormat('d. F Y');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    {{-- Date Navigation --}}
    <div class="flex items-center justify-between">
        <flux:button wire:click="previousDay" variant="outline">&larr; Vorheriger Tag</flux:button>
        <h1 class="text-xl font-bold tracking-tight text-center">
            {{ $this->formattedDate }}
        </h1>
        <flux:button wire:click="nextDay" variant="outline" :disabled="\Carbon\Carbon::parse($date)->isToday()">Nächster Tag &rarr;</flux:button>
    </div>
    
    {{-- The Calorie Tracker Component --}}
    <div class="mt-4">
        <livewire:components.calorie-tracker :date="$date" wire:key="$date" />
    </div>
</div>