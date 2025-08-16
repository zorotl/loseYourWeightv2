<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('components.layouts.app')]
class extends Component
{
    use WithPagination;

    public function removeFavorite(int $foodId): void
    {
        auth()->user()->favoriteFoods()->detach($foodId);
        // Die Seite wird durch das re-rendering automatisch aktualisiert.
        $this->dispatch('show-toast', message: 'Favorit entfernt.');
    }

    public function with(): array
    {
        return [
            'favoriteFoods' => auth()->user()->favoriteFoods()->paginate(15),
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex flex-col gap-1.5">
        <h1 class="text-2xl font-bold tracking-tight">
            {{ __('Meine Favoriten') }}
        </h1>
        <p class="text-sm text-zinc-500">
            {{ __('Deine Lieblingslebensmittel für den schnellen Zugriff.') }}
        </p>
    </div>

    <div class="flow-root">
        <ul role="list" class="-my-5 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($favoriteFoods as $food)
                <li class="py-4" wire:key="{{ $food->id }}">
                    <div class="flex items-center space-x-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $food->name }}</p>
                            <p class="truncate text-sm text-gray-500">{{ $food->brand ?? 'Keine Marke' }} - {{ $food->calories }} kcal / 100g</p>
                        </div>
                        <div>
                           <button wire:click="removeFavorite({{ $food->id }})" wire:confirm="Diesen Favoriten wirklich entfernen?" class="text-sm text-red-500 hover:text-red-700">
                                Entfernen
                           </button>
                        </div>
                    </div>
                </li>
            @empty
                <li class="py-4 text-center text-sm text-gray-500">
                    Du hast noch keine Favoriten markiert. Klicke auf den Stern ⭐ neben einem Eintrag in deinem Tagesprotokoll.
                </li>
            @endforelse
        </ul>
    </div>

    <div class="mt-4">
        {{ $favoriteFoods->links() }}
    </div>
</div>