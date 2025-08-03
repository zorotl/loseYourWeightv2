<?php

use App\Models\Meal;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public Collection $meals;

    #[Rule('required|string|max:100')]
    public string $newMealName = '';

    public function mount(): void
    {
        $this->loadMeals();
    }

    public function createMeal(): void
    {
        $validated = $this->validate();

        auth()
            ->user()
            ->meals()
            ->create([
                'name' => $validated['newMealName'],
            ]);

        $this->newMealName = '';
        $this->loadMeals();
    }

    public function loadMeals(): void
    {
        // We eager load the 'foods' relationship to count ingredients efficiently.
        $this->meals = auth()->user()->meals()->with('foods')->get();
    }

    public function deleteMeal(int $mealId): void
    {
        $meal = Meal::findOrFail($mealId);
        $this->authorize('delete', $meal);
        $meal->delete();
        $this->loadMeals();
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex flex-col gap-1.5">
        <h1 class="text-2xl font-bold tracking-tight">
            {{ __('Meine Mahlzeiten') }}
        </h1>
        <p class="text-sm text-zinc-500">
            {{ __('Hier kannst du deine Standard-Mahlzeiten verwalten, um sie schneller zu loggen.') }}
        </p>
    </div>

    {{-- Form to create a new meal --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Neue Mahlzeit erstellen</h3>
        <form wire:submit="createMeal" class="mt-4 flex items-end gap-4">
            <div class="flex-1">
                <flux:input wire:model="newMealName" :label="__('Name der Mahlzeit')" required />
            </div>
            <div>
                <flux:button type="submit" variant="primary">
                    {{ __('Erstellen') }}
                </flux:button>
            </div>
        </form>
    </div>

    {{-- List of existing meals --}}
    <div class="mt-4 flow-root">
        <ul role="list" class="-my-5 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($meals as $meal)
                <li class="py-4" wire:key="{{ $meal->id }}">
                    <div class="flex items-center space-x-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $meal->name }}
                            </p>
                            <p class="truncate text-sm text-gray-500">{{ $meal->foods_count }} Zutaten - Total {{ $meal->total_calories }} kcal
                            </p>
                        </div>
                        <div>
                            <flux:button variant="outline" :href="route('pages.meals.show', ['meal' => $meal])" wire:navigate>
                                Bearbeiten
                            </flux:button>
                            <button wire:click="deleteMeal({{ $meal->id }})" wire:confirm="Bist du sicher, dass du diese Mahlzeit löschen willst?" class="ml-2 text-sm text-red-500 hover:text-red-700">
                                Löschen
                            </button>
                        </div>
                    </div>
                </li>
            @empty
                <li class="py-4 text-center text-sm text-gray-500">
                    Du hast noch keine Mahlzeiten erstellt. Isst du überhaupt?
                </li>
            @endforelse
        </ul>
    </div>
</div>
