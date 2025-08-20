<?php

use App\Models\Feedback;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('components.layouts.app')]
class extends Component
{
    use WithPagination;

    public string $statusFilter = 'neu';

    public function mount(): void
    {
        Gate::authorize('view-admin-panel');
    }

    public function setStatus(int $feedbackId, string $status): void
    {
        $feedback = Feedback::findOrFail($feedbackId);
        $feedback->update(['status' => $status]);
        $this->dispatch('show-toast', message: 'Status aktualisiert.');
    }

    public function with(): array
    {
        $feedback = Feedback::with('user')
            ->when($this->statusFilter !== 'alle', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('priority', 'desc')
            ->latest()
            ->paginate(10);
            
        return ['feedbackItems' => $feedback];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <h1 class="text-2xl font-bold tracking-tight">Admin: Feedback-Eingang</h1>

    {{-- Filter Buttons --}}
    <div class="flex space-x-2 rounded-lg bg-gray-100 p-1 dark:bg-zinc-800">
        <button wire:click="$set('statusFilter', 'neu')" class="w-full rounded-md px-3 py-2 text-sm font-medium {{ $statusFilter === 'neu' ? 'bg-white text-gray-900 shadow dark:bg-zinc-700 dark:text-white' : 'text-gray-600 hover:bg-white/50 dark:text-gray-400 dark:hover:bg-zinc-700/50' }}">
            Neu
        </button>
        <button wire:click="$set('statusFilter', 'akzeptiert')" class="w-full rounded-md px-3 py-2 text-sm font-medium {{ $statusFilter === 'akzeptiert' ? 'bg-white text-gray-900 shadow dark:bg-zinc-700 dark:text-white' : 'text-gray-600 hover:bg-white/50 dark:text-gray-400 dark:hover:bg-zinc-700/50' }}">
            Akzeptiert
        </button>
        <button wire:click="$set('statusFilter', 'abgelehnt')" class="w-full rounded-md px-3 py-2 text-sm font-medium {{ $statusFilter === 'abgelehnt' ? 'bg-white text-gray-900 shadow dark:bg-zinc-700 dark:text-white' : 'text-gray-600 hover:bg-white/50 dark:text-gray-400 dark:hover:bg-zinc-700/50' }}">
            Abgelehnt
        </button>
         <button wire:click="$set('statusFilter', 'alle')" class="w-full rounded-md px-3 py-2 text-sm font-medium {{ $statusFilter === 'alle' ? 'bg-white text-gray-900 shadow dark:bg-zinc-700 dark:text-white' : 'text-gray-600 hover:bg-white/50 dark:text-gray-400 dark:hover:bg-zinc-700/50' }}">
            Alle
        </button>
    </div>

    {{-- Feedback List --}}
    <div class="space-y-4">
        @forelse($feedbackItems as $item)
            <div wire:key="{{ $item->id }}" class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-x-3">
                            <span @class([
                                'rounded-full px-2 py-1 text-xs font-medium',
                                'bg-blue-100 text-blue-800' => $item->priority === 3, // Bug
                                'bg-purple-100 text-purple-800' => $item->priority === 2, // Feature
                                'bg-gray-100 text-gray-800' => $item->priority === 1, // General
                            ])>{{ $item->type }}</span>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->created_at->diffForHumans() }}</p>
                        </div>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ $item->message }}</p>
                        <div class="mt-2 text-xs text-gray-400">
                            <span>URL: {{ $item->url_at_submission }}</span>
                        </div>
                    </div>
                    @if($item->status === 'neu')
                        <div class="flex flex-shrink-0 space-x-2">
                             <flux:button wire:click="setStatus({{ $item->id }}, 'akzeptiert')" variant="outline" size="sm">Akzeptieren</flux:button>
                             <flux:button wire:click="setStatus({{ $item->id }}, 'abgelehnt')" variant="outline" size="sm">Ablehnen</flux:button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
             <div class="rounded-xl border-2 border-dashed border-neutral-200 p-12 text-center dark:border-neutral-700">
                <p class="text-zinc-500">Keine Feedback-Einträge in dieser Kategorie.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $feedbackItems->links() }}
    </div>
</div>