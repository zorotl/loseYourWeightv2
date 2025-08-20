<?php

use App\Models\Food;
use App\Models\Meal;
use Illuminate\Database\Eloquent\Collection;
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

    // Properties for editing existing ingredients
    #[Rule('required|string|max:100')]
    public string $mealName;
    
    public array $quantities = [];

    // Properties for adding new ingredients
    public string $addIngredientTab = 'search'; // 'search' or 'favorites'
    public string $search = '';
    public bool $apiSearched = false;
    public Collection|array $searchResults = [];
    public ?array $selectedFood = null;
    public ?Collection $favoriteFoods = null;
    
    // Quantity for the 'Add Ingredient' forms
    #[Rule('required|integer|min:1|max:5000')]
    public int|string $addQuantity = '';

    public array $favoriteQuantities = [];

    public function mount(Meal $meal): void
    {
        $this->authorize('view', $meal);
        $this->meal->load('foods');
        $this->mealName = $this->meal->name;
        $this->updateQuantitiesArray();
        $this->favoriteFoods = auth()->user()->favoriteFoods;
        $this->searchResults = new Collection();
    }

    public function updateName(): void
    {
        $this->validateOnly('mealName');
        $this->meal->update(['name' => $this->mealName]);
        $this->dispatch('show-toast', message: 'Name aktualisiert.');
    }

    public function updateQuantity(int $foodId): void
    {
        $this->validate(['quantities.'.$foodId => 'required|integer|min:1|max:9999']);
        $this->meal->foods()->updateExistingPivot($foodId, [
            'quantity_grams' => $this->quantities[$foodId]
        ]);
        $this->meal->load('foods'); // Reload to recalculate total calories
    }

    public function addIngredient(): void
    {
        $this->validateOnly('addQuantity');
        if (!$this->selectedFood) return;

        $food = Food::firstOrCreate(
            ['source' => $this->selectedFood['source'], 'source_id' => $this->selectedFood['source_id']],
            ['name' => $this->selectedFood['name'], 'brand' => $this->selectedFood['brand'], 'calories' => $this->selectedFood['calories']]
        );

        $this->meal->foods()->syncWithoutDetaching([$food->id => ['quantity_grams' => $this->addQuantity]]);
        $this->reset('search', 'searchResults', 'selectedFood', 'addQuantity');
        $this->meal->load('foods');
        $this->updateQuantitiesArray();
    }

    public function addFavoriteAsIngredient(int $foodId): void
    {
        $this->validate(['favoriteQuantities.' . $foodId => 'required|integer|min:1|max:5000']);
        $quantity = $this->favoriteQuantities[$foodId];

        $this->meal->foods()->syncWithoutDetaching([$foodId => ['quantity_grams' => $quantity]]);
        
        unset($this->favoriteQuantities[$foodId]);
        $this->meal->load('foods');
        $this->updateQuantitiesArray();
        $this->dispatch('show-toast', message: 'Zutat hinzugefügt.');
    }

    public function removeIngredient(int $foodId): void
    {
        $this->meal->foods()->detach($foodId);
        $this->meal->load('foods');
        $this->updateQuantitiesArray();
    }

    protected function updateQuantitiesArray(): void
    {
        $this->quantities = $this->meal->foods->pluck('pivot.quantity_grams', 'id')->all();
    }
    
    public function updatedSearch(string $value): void
    {
        $this->selectedFood = null;
        $this->apiSearched = false;
        if (strlen($value) < 3) {
            $this->searchResults = new Collection();
            return;
        }
        $this->searchResults = Food::query()
            ->where('name', 'like', '%' . $value . '%')
            ->orWhere('brand', 'like', '%' . $value . '%')
            ->take(5)
            ->get();
    }
    
    public function searchApi(): void
    {
        if (strlen($this->search) < 3) return;
        try {
            $response = Http::timeout(4)->get('https://world.openfoodfacts.org/cgi/search.pl', [
                'search_terms' => $this->search, 'search_simple' => 1, 'action' => 'process', 'json' => 1, 'page_size' => 5,
            ]);
            if ($response->ok()) {
                $this->searchResults = $response->json()['products'] ?? [];
                $this->apiSearched = true;
            } else {
                $this->searchResults = [];
            }
        } catch (ConnectionException $e) {
            $this->searchResults = [];
            $this->dispatch('show-toast', message: 'Die Lebensmittel-API ist nicht erreichbar.', type: 'error');
        }
    }
    
    public function selectFood($foodData, bool $isApiResult = false): void
    {
        $foodDetails = [];
        if ($isApiResult) {
            $productData = collect($this->searchResults)->firstWhere('code', $foodData);
            if (!$productData) return;
            $calories = data_get($productData, 'nutriments.energy-kcal_100g');
            if (!$calories) {
                $this->dispatch('show-toast', message: 'Dieses Produkt hat keine Kalorienangaben.', type: 'error');
                return;
            }
            $foodDetails = [
                'source' => 'openfoodfacts', 'source_id' => $productData['code'],
                'name' => data_get($productData, 'product_name', 'Unknown'),
                'brand' => data_get($productData, 'brands', 'Unknown'),
                'calories' => (int) $calories,
                'protein' => (float) data_get($productData, 'nutriments.proteins_100g', 0),
                'carbohydrates' => (float) data_get($productData, 'nutriments.carbohydrates_100g', 0),
                'fat' => (float) data_get($productData, 'nutriments.fat_100g', 0),
            ];
        } else {
            $food = Food::find($foodData);
            if (!$food) return;
            $foodDetails = [
                'source' => $food->source, 'source_id' => $food->source_id,
                'name' => $food->name, 'brand' => $food->brand,
                'calories' => $food->calories, 'protein' => $food->protein,
                'carbohydrates' => $food->carbohydrates, 'fat' => $food->fat,
            ];
        }
        $this->selectedFood = $foodDetails;
        $this->searchResults = new Collection();
        $this->search = $this->selectedFood['name'];
    }
    
    #[Computed]
    public function totalCalories(): int
    {
        return round($this->meal->foods->reduce(function ($carry, $food) {
            $caloriesPerGram = $food->calories / 100;
            $ingredientCalories = $caloriesPerGram * $food->pivot->quantity_grams;
            return $carry + $ingredientCalories;
        }, 0));
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <div>
        <a href="{{ route('pages.meals.index') }}" wire:navigate class="text-sm text-indigo-600 hover:underline">&larr; Zurück zur Übersicht</a>
    </div>

    <div x-data="{ editing: false }">
        <div 
            x-show="!editing" 
            @click="editing = true; $nextTick(() => $refs.mealNameInput.focus())"
            class="inline-flex cursor-pointer items-center gap-2 rounded-lg p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
        >
            <h1 class="text-2xl font-bold tracking-tight">
                Mahlzeit: <span class="text-indigo-700">{{ $mealName }}</span>
            </h1>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
            </svg>
        </div>
        {{-- FIX: Added $wire.updateName() to the click.away event --}}
        <div x-show="editing" @click.away="editing = false; $wire.updateName()" x-cloak>
            <flux:input wire:model="mealName" x-ref="mealNameInput" @keydown.enter="editing = false; $wire.updateName()" @keydown.escape="editing = false" />
        </div>
        <p class="mt-2 text-sm text-zinc-500">
            Gesamtkalorien: <span class="font-bold">{{ $this->totalCalories() }} kcal</span>
        </p>
    </div>

    <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Zutat hinzufügen</h3>
        
        <div class="mt-4 border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="$wire.set('addIngredientTab', 'search')" 
                        class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
                        :class="$wire.addIngredientTab === 'search' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300'">
                    Suche
                </button>
                <button @click="$wire.set('addIngredientTab', 'favorites')"
                        class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
                        :class="$wire.addIngredientTab === 'favorites' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300'">
                    Favoriten
                </button>
            </nav>
        </div>

        <div x-show="$wire.addIngredientTab === 'search'" x-cloak>
            <div class="relative mt-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Suche zuerst in deiner lokalen Datenbank..." />
                                
                @if(strlen($search) >= 3 && count($searchResults) > 0)
                    <div class="absolute z-10 mt-1 w-full rounded-md border border-gray-300 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800">
                        <ul class="max-h-72 divide-y divide-gray-200 dark:divide-gray-700 overflow-auto rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                            @foreach($searchResults as $result)
                                @if($apiSearched)
                                    <li wire:click="selectFood('{{ $result['code'] }}', true)" class="relative cursor-pointer select-none py-2 px-4 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                                        <span class="block truncate">{{ data_get($result, 'product_name', 'Unbekannt') }}</span>
                                        <span class="block truncate text-xs text-gray-500">{{ data_get($result, 'brands', 'Unbekannte Marke') }}</span>
                                    </li>
                                @else
                                    <li wire:click="selectFood({{ $result->id }}, false)" class="relative cursor-pointer select-none py-2 px-4 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                                        <div class="flex justify-between">
                                            <span>
                                                <span class="block truncate font-medium">{{ $result->name }}</span>
                                                <span class="block truncate text-xs text-gray-500">{{ $result->brand }}</span>
                                            </span>
                                            <span class="text-xs text-green-600">Lokal</span>
                                        </div>
                                    </li>
                                @endif
                            @endforeach

                            @if(!$apiSearched && !$selectedFood)
                                <li>
                                    <button wire:click="searchApi" class="w-full bg-indigo-50 py-3 px-4 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/50 dark:text-indigo-300 dark:hover:bg-indigo-900">
                                        Nicht gefunden? In der Online-Datenbank suchen...
                                    </button>
                                </li>
                            @endif
                        </ul>
                    </div>
                @elseif(strlen($search) >= 3 && !$apiSearched && !$selectedFood)
                    <div class="absolute z-10 mt-1 w-full rounded-md border border-gray-300 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800">
                        <button wire:click="searchApi" class="w-full py-3 px-4 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/50 dark:text-indigo-300 dark:hover:bg-indigo-900">
                            Nichts lokales gefunden. Online suchen...
                        </button>
                    </div>
                @endif
            </div>
            @if($selectedFood)
                <form wire:submit.prevent="addIngredient" class="mt-4 flex items-end gap-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                    <div class="flex-1">
                        <p class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ausgewähltes Lebensmittel</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $selectedFood['name'] }} ({{ $selectedFood['calories'] }} kcal/100g)</p>
                    </div>
                    <div class="w-32">
                        <flux:input wire:model="addQuantity" :label="__('Menge (g)')" type="number" required />
                    </div>
                    <div>
                        <flux:button type="submit" variant="primary">{{ __('Hinzufügen') }}</flux:button>
                    </div>
                </form>
            @endif
        </div>

        <div x-show="$wire.addIngredientTab === 'favorites'" x-cloak class="mt-4">
            <div class="space-y-2">
                @forelse($favoriteFoods as $food)
                    <div wire:key="fav-ingredient-{{ $food->id }}">
                        <form wire:submit.prevent="addFavoriteAsIngredient({{ $food->id }})" class="flex items-end gap-4 rounded-lg p-2 even:bg-gray-50 dark:even:bg-gray-900/50">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $food->name }}</p>
                                <p class="text-xs text-gray-500">{{ $food->calories }} kcal / 100g</p>
                            </div>
                            <div class="w-32">
                                {{-- FIX: Use the new favoriteQuantities array property --}}
                                <flux:input wire:model="favoriteQuantities.{{ $food->id }}" :label="__('Menge (g)')" type="number" required />
                            </div>
                            <div>
                                <flux:button type="submit" variant="primary">{{ __('Hinzufügen') }}</flux:button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-gray-500">Keine Favoriten zum Hinzufügen vorhanden.</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="flow-root">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Zutaten</h3>
        <ul role="list" class="mt-4 -my-5 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($meal->foods as $food)
                <li class="py-4" wire:key="food-{{ $food->id }}">
                    <div class="flex items-center space-x-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $food->name }}</p>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="number" 
                                    wire:model="quantities.{{ $food->id }}" 
                                    wire:keydown.enter="updateQuantity({{ $food->id }})" 
                                    wire:blur="updateQuantity({{ $food->id }})" 
                                    class="w-20 rounded-md border-gray-300 px-2 py-1 text-sm shadow-sm transition-colors hover:bg-gray-50 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
                                >
                                <span class="text-sm text-gray-500">g</span>
                            </div>
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