<?php

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.app')]
class extends Component
{
    public string $activeTab = 'chart';
    public Collection $history;

    public function mount(): void
    {
        $this->history = auth()->user()->weightHistories()->orderBy('weighed_on', 'desc')->get();
    }

}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <h1 class="text-2xl font-bold tracking-tight">Statistiken</h1>

    {{-- Tab Navigation --}}
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button @click="$wire.set('activeTab', 'chart')" :class="{ 'border-indigo-500 text-indigo-600': $wire.activeTab === 'chart' }" class="whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                Grafik
            </button>
            <button @click="$wire.set('activeTab', 'table')" :class="{ 'border-indigo-500 text-indigo-600': $wire.activeTab === 'table' }" class="whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                Tabelle
            </button>
        </nav>
    </div>

    {{-- Chart View --}}
    <div x-show="$wire.activeTab === 'chart'" x-cloak>
        <livewire:components.weight-chart />
    </div>

    {{-- Table View --}}
    <div x-show="$wire.activeTab === 'table'" x-cloak>
        <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Datum</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Gewicht (kg)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-900">
                    @forelse($history as $entry)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500">{{ $entry->weighed_on->format('d. F Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">{{ $entry->weight_kg }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">Keine Gewichtsdaten vorhanden.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>