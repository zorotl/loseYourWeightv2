<?php

use App\Models\Food;
use App\Models\Meal;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.app')]
class extends Component
{
    public Meal $meal;

    // Search properties
    public string $search = '';
    public array $searchResults = [];
    public ?array $selectedFood = null;

    // Add ingredient property
    #[Rule('required|integer|min:1|max:5000')]
    public int|string $quantity = '';

    public function mount(Meal $meal): void
    {
        $this->authorize('view', $meal);
        $this->meal->load('foods');
    }

    // Almost identical to the calorie-tracker search
    public function updatedSearch(string $value): void
    {
        if (strlen($value) < 3) {
            $this->searchResults = [];
            $this->selectedFood = null;
            return;
        }
        $response = Http::get('https://world.openfoodfacts.org/cgi/search.pl', [
            'search_terms' => $value, 'search_simple' => 1, 'action' => 'process', 'json' => 1, 'page_size' => 5,
        ]);
        $this->searchResults = $response->ok() ? $response->json()['products'] ?? [] : [];
    }
    
    // Identical to the calorie-tracker select
    public function selectFood(string $productCode): void
    {
        $productData = collect($this->searchResults)->firstWhere('code', $productCode);
        if (!$productData) return;

        $calories = data_get($productData, 'nutriments.energy-kcal_100g');
        if (!$calories) return;

        $this->selectedFood = [
            'source' => 'openfoodfacts',
            'source_id' => $productData['code'],
            'name' => data_get($productData, 'product_name', 'Unknown'),
            'brand' => data_get($productData, 'brands', 'Unknown'),
            'calories' => (int) $calories,
        ];
        
        $this->searchResults = [];
        $this->search = $this->selectedFood['name'];
    }

    // New method to add the selected food as an ingredient to the meal
    public function addIngredient(): void
    {
        $this->validateOnly('quantity');
        if (!$this->selectedFood) return;

        $food = Food::firstOrCreate(
            ['source' => $this->selectedFood['source'], 'source_id' => $this->selectedFood['source_id']],
            ['name' => $this->selectedFood['name'], 'brand' => $this->selectedFood['brand'], 'calories' => $this->selectedFood['calories']]
        );

        // Attach the food to the meal using the pivot table
        $this->meal->foods()->attach($food->id, ['quantity_grams' => $this->quantity]);

        $this->reset('search', 'searchResults', 'selectedFood', 'quantity');
        $this->meal->load('foods'); // Reload the ingredients list
    }

    // New method to remove an ingredient from the meal
    public function removeIngredient(int $foodId): void
    {
        $this->meal->foods()->detach($foodId);
        $this->meal->load('foods'); // Reload the ingredients list
    }

    #[Computed]
    public function totalCalories(): int
    {
        // This computed property will automatically recalculate when the ingredients change
        return round($this->meal->foods->reduce(function ($carry, $food) {
            $caloriesPerGram = $food->calories / 100;
            $ingredientCalories = $caloriesPerGram * $food->pivot->quantity_grams;
            return $carry + $ingredientCalories;
        }, 0));
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div>
        <a href="{{ route('pages.meals.index') }}" wire:navigate class="text-sm text-indigo-600 hover:underline">&larr; Zurück zur Übersicht</a>
    </div>

    <div class="flex flex-col gap-1.5">
        <h1 class="text-2xl font-bold tracking-tight">
            Mahlzeit bearbeiten: <span class="text-indigo-600">{{ $meal->name }}</span>
        </h1>
        <p class="text-sm text-zinc-500">
            Gesamtkalorien: <span class="font-bold">{{ $this->totalCalories() }} kcal</span>
        </p>
    </div>

    {{-- The ingredient search and add form --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Zutat hinzufügen</h3>
        
        <div class="relative mt-4">
            <flux:input wire:model.live.debounce.500ms="search" :label="__('Lebensmittel suchen...')" placeholder="z.B. Pouletbrust" />

            @if(!empty($searchResults))
                <div class="absolute z-10 mt-1 w-full rounded-md border border-gray-300 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800">
                    <ul class="max-h-60 overflow-auto rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                        @foreach($searchResults as $product)
                            <li wire:click="selectFood('{{ $product['code'] }}')" class="relative cursor-pointer select-none py-2 px-4 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                                <span class="block truncate">{{ data_get($product, 'product_name', 'Unbekannt') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        
        @if($selectedFood)
            <form wire:submit="addIngredient" class="mt-4 flex items-end gap-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                <div class="flex-1">
                    <p class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ausgewähltes Lebensmittel</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $selectedFood['name'] }} ({{ $selectedFood['calories'] }} kcal/100g)</p>
                </div>
                <div class="w-32">
                    <flux:input wire:model="quantity" :label="__('Menge (g)')" type="number" required />
                </div>
                <div>
                    <flux:button type="submit" variant="primary">{{ __('Hinzufügen') }}</flux:button>
                </div>
            </form>
        @endif
    </div>
    
    {{-- List of existing ingredients --}}
    <div class="flow-root">
        <h3 class="mt-6 text-lg font-medium text-gray-900 dark:text-white">Zutaten</h3>
        <ul role="list" class="mt-4 -my-5 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($meal->foods as $food)
                <li class="py-4" wire:key="food-{{ $food->id }}">
                    <div class="flex items-center space-x-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $food->name }}</p>
                            <p class="truncate text-sm text-gray-500">{{ $food->pivot->quantity_grams }}g</p>
                        </div>
                        <div>
                           <button wire:click="removeIngredient({{ $food->id }})" class="text-sm text-red-500 hover:text-red-700">
                                Entfernen
                           </button>
                        </div>
                    </div>
                </li>
            @empty
                <li class="py-4 text-center text-sm text-gray-500">
                    Diese Mahlzeit hat noch keine Zutaten. Ein sehr minimalistisches Rezept.
                </li>
            @endforelse
        </ul>
    </div>
</div>