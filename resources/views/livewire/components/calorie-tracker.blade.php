<?php

use App\Models\Food;
use App\Models\FoodLogEntry;
use App\Models\Meal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public Collection $todaysEntries;
    public string $date;

    // State properties
    public string $activeTab = 'food';
    public bool $apiSearched = false;

    // Food Search properties
    public string $search = '';
    public Collection|array $searchResults;
    public ?array $selectedFood = null;

    // Quantity properties
    #[Rule('required|integer|min:1|max:5000')]
    public int|string $quantity = '';
    public array $favoriteQuantities = [];
    public array $entryQuantities = [];

    // Meal Search properties
    public string $mealSearch = '';
    public ?Collection $mealSearchResults = null;

    // Favorites properties
    public string $favoriteSearch = '';
    public ?Collection $favoriteFoods = null;
    public array $favoriteFoodIds = [];

    // Manual entry properties
    #[Rule('required|string|max:100')]
    public string $manualFoodName = '';
    #[Rule('required|integer|min:0|max:5000')]
    public int|string $manualCaloriesPer100g = '';
    public bool $showManualForm = false;

    public function mount($date = null): void
    {
        $this->date = $date ? Carbon::parse($date)->toDateString() : today()->toDateString();
        $this->loadEntries();
        $this->mealSearchResults = auth()->user()->meals;
        $this->loadFavorites();
        $this->searchResults = new Collection();
    }
    
    public function updatedDate($value): void
    {
        $this->loadEntries();
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
                'search_terms' => $this->search, 'search_simple' => 1, 'action' => 'process', 'json' => 1, 'page_size' => 10,
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

    protected function loadFavorites(): void
    {
        $user = Auth::user();
        $this->favoriteFoods = $user->favoriteFoods()
            ->when($this->favoriteSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->favoriteSearch . '%');
            })
            ->get();
        $this->favoriteFoodIds = $user->favoriteFoods()->pluck('food_id')->toArray();
    }

    public function updatedFavoriteSearch(): void
    {
        $this->loadFavorites();
    }

    public function toggleFavorite(int $foodId): void
    {
        auth()->user()->favoriteFoods()->toggle($foodId);
        $this->loadFavorites();
        $this->dispatch('show-toast', message: 'Favoriten aktualisiert.');
    }

    public function logFavorite(int $foodId): void
    {
        $this->validate(['favoriteQuantities.'.$foodId => 'required|integer|min:1|max:5000']);
        $food = Food::find($foodId);
        if (!$food) return;
        $quantityToLog = $this->favoriteQuantities[$foodId];
        auth()->user()->foodLogEntries()->create([
            'food_id' => $food->id, 'quantity_grams' => $quantityToLog,
            'calories' => round(($food->calories / 100) * $quantityToLog),
            'consumed_at' => Carbon::parse($this->date)->setTimeFrom(now()),
        ]);
        unset($this->favoriteQuantities[$foodId]);
        $this->loadEntries();
        $this->dispatch('food-logged');
        $this->dispatch('show-toast', message: "'{$food->name}' hinzugefügt.");
    }

    public function logSelectedFood(): void
    {
        $this->validateOnly('quantity');
        if (!$this->selectedFood) return;
        $food = Food::firstOrCreate(
            ['source' => $this->selectedFood['source'], 'source_id' => $this->selectedFood['source_id']],
            ['name' => $this->selectedFood['name'], 'brand' => $this->selectedFood['brand'], 'calories' => $this->selectedFood['calories'], 'protein' => $this->selectedFood['protein'], 'carbohydrates' => $this->selectedFood['carbohydrates'], 'fat' => $this->selectedFood['fat']]
        );
        auth()->user()->foodLogEntries()->create([
            'food_id' => $food->id, 'quantity_grams' => $this->quantity,
            'calories' => round(($food->calories / 100) * $this->quantity),
            'consumed_at' => Carbon::parse($this->date)->setTimeFrom(now()),
        ]);
        $this->reset('selectedFood', 'quantity', 'search');
        $this->loadEntries();
        $this->dispatch('food-logged');
    }

    public function logManualFood(): void
    {
        $validated = $this->validate(['manualFoodName' => 'required|string|max:100', 'manualCaloriesPer100g' => 'required|integer|min:0|max:5000', 'quantity' => 'required|integer|min:1|max:5000']);
        $food = Food::firstOrCreate(
            ['name' => $validated['manualFoodName'], 'creator_id' => auth()->id(), 'source' => 'user'],
            ['calories' => $validated['manualCaloriesPer100g']]
        );
        auth()->user()->foodLogEntries()->create([
            'food_id' => $food->id, 'quantity_grams' => $validated['quantity'],
            'calories' => round(($food->calories / 100) * $validated['quantity']),
            'consumed_at' => Carbon::parse($this->date)->setTimeFrom(now()),
        ]);
        $this->reset('manualFoodName', 'manualCaloriesPer100g', 'quantity');
        $this->showManualForm = false;
        $this->loadEntries();
        $this->dispatch('food-logged');
    }

    public function updatedMealSearch(string $value): void
    {
        $this->mealSearchResults = auth()->user()->meals()->where('name', 'like', '%' . $value . '%')->get();
    }

    public function logMeal(int $mealId): void
    {
        $meal = auth()->user()->meals()->with('foods')->find($mealId);
        if (!$meal) return;
        $logEntries = [];
        $now = now();
        $consumedAt = Carbon::parse($this->date)->setTimeFrom($now);
        foreach ($meal->foods as $food) {
            $logEntries[] = [
                'user_id' => auth()->id(), 'food_id' => $food->id,
                'quantity_grams' => $food->pivot->quantity_grams,
                'calories' => round(($food->calories / 100) * $food->pivot->quantity_grams),
                'consumed_at' => $consumedAt, 'created_at' => $now, 'updated_at' => $now,
            ];
        }
        if (!empty($logEntries)) { FoodLogEntry::insert($logEntries); }
        $this->loadEntries();
        $this->activeTab = 'food';
        $this->dispatch('food-logged');
    }

    public function deleteEntry(int $entryId): void
    {
        $entry = FoodLogEntry::where('id', $entryId)->where('user_id', auth()->id())->first();
        if ($entry) {
            $entry->delete();
            $this->loadEntries();
            $this->dispatch('food-logged');
        }
    }
    
    public function updateEntryQuantity(int $entryId): void
    {
        $this->validate(['entryQuantities.' . $entryId => 'required|integer|min:1|max:9999']);
        $entry = FoodLogEntry::where('id', $entryId)->where('user_id', auth()->id())->first();
        if ($entry) {
            $newQuantity = $this->entryQuantities[$entryId];
            $recalculatedCalories = round(($entry->food->calories / 100) * $newQuantity);
            $entry->update(['quantity_grams' => $newQuantity, 'calories' => $recalculatedCalories]);
            $this->loadEntries();
            $this->dispatch('food-logged');
            $this->dispatch('show-toast', message: 'Eintrag aktualisiert.');
        }
    }

    public function loadEntries(): void
    {
        $this->todaysEntries = FoodLogEntry::with('food')
            ->where('user_id', auth()->id())
            ->whereDate('consumed_at', $this->date)
            ->orderBy('consumed_at', 'desc')
            ->get();
        $this->entryQuantities = $this->todaysEntries->pluck('quantity_grams', 'id')->all();
    }

    #[Computed]
    public function consumedCalories(): int { return $this->todaysEntries->sum('calories'); }
    #[Computed]
    public function remainingCalories(): int { return auth()->user()->target_calories - $this->consumedCalories(); }
    #[Computed]
    public function caloriesPercentage(): int
    {
        $target = auth()->user()->target_calories;
        return $target > 0 ? min(100, round(($this->consumedCalories() / $target) * 100)) : 0;
    }

}; ?>

<div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800" wire:key="calorie-tracker-{{ $date }}">
    <div class="flex flex-col gap-2">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ \Carbon\Carbon::parse($date)->isToday() ? 'Heutige Kalorien' : 'Kalorien vom ' . \Carbon\Carbon::parse($date)->translatedFormat('d. F') }}
        </h3>
        <p class="text-sm text-gray-500">
            Verbraucht: <span class="font-bold">{{ $this->consumedCalories() }}</span> / {{ auth()->user()->target_calories }} kcal
        </p>
        <div class="w-full pt-1">
            <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                <div class="h-2.5 rounded-full bg-indigo-600" style="width: {{ $this->caloriesPercentage() }}%"></div>
            </div>
        </div>
    </div>

    <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="$set('activeTab', 'food')" class="{{ $activeTab === 'food' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Lebensmittel
                </button>
                <button wire:click="$set('activeTab', 'favorites')" class="{{ $activeTab === 'favorites' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Favoriten
                </button>
                <button wire:click="$set('activeTab', 'meal')" class="{{ $activeTab === 'meal' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Mahlzeiten
                </button>
            </nav>
        </div>

        <div x-show="$wire.activeTab === 'food'" x-cloak class="mt-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Lebensmittel hinzufügen</h3>
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
                <form wire:submit.prevent="logSelectedFood" class="mt-4 flex items-end gap-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
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
            <div class="mt-4 text-center">
                <button wire:click="$toggle('showManualForm')" class="text-sm text-indigo-600 hover:underline">
                    {{ $showManualForm ? 'Suche verwenden' : 'Produkt nicht gefunden? Manuell eintragen.' }}
                </button>
            </div>
            @if($showManualForm)
                <form wire:submit.prevent="logManualFood" class="mt-4 flex items-end gap-4 rounded-lg border border-dashed border-gray-400 p-4">
                    <div class="flex-1">
                        <flux:input wire:model="manualFoodName" :label="__('Lebensmittel')" required />
                    </div>
                    <div class="w-48">
                        <flux:input wire:model="manualCaloriesPer100g" :label="__('Kalorien pro 100g')" type="number" required />
                    </div>
                    <div class="w-32">
                        <flux:input wire:model="quantity" :label="__('Menge (g)')" type="number" required />
                    </div>
                    <div>
                        <flux:button type="submit" variant="outline">{{ __('Manuell hinzufügen') }}</flux:button>
                    </div>
                </form>
            @endif
        </div>
        
        <div x-show="$wire.activeTab === 'favorites'" x-cloak class="mt-4">
             <h3 class="text-base font-semibold text-gray-900 dark:text-white">Aus deinen Favoriten hinzufügen</h3>
            <div class="mt-4">
                <flux:input wire:model.live.debounce.300ms="favoriteSearch" placeholder="Favoriten durchsuchen..." />
            </div>
            <div class="mt-4 space-y-2">
                @forelse($favoriteFoods as $food)
                    <div wire:key="fav-{{ $food->id }}">
                        <form wire:submit.prevent="logFavorite({{ $food->id }})" class="flex items-end gap-4 rounded-lg p-2 even:bg-gray-50 dark:even:bg-gray-900/50">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $food->name }}</p>
                                <p class="text-xs text-gray-500">{{ $food->calories }} kcal / 100g</p>
                            </div>
                            <div class="w-32">
                                <flux:input wire:model="favoriteQuantities.{{ $food->id }}" :label="__('Menge (g)')" type="number" required />
                            </div>
                            <div>
                                <flux:button type="submit" variant="primary">{{ __('Hinzufügen') }}</flux:button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-gray-500">Du hast noch keine Favoriten. Füge sie aus deiner Tagesliste hinzu.</p>
                @endforelse
            </div>
        </div>

        <div x-show="$wire.activeTab === 'meal'" x-cloak class="mt-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Gespeicherte Mahlzeiten hinzufügen</h3>
            <div class="mt-4">
                <flux:input wire:model.live.debounce.300ms="mealSearch" placeholder="Mahlzeit suchen..." />
                <div class="mt-4 flow-root">
                    <ul role="list" class="-my-5 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($mealSearchResults as $meal)
                            <li class="py-4" wire:key="meal-{{ $meal->id }}">
                                <div class="flex items-center space-x-4">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $meal->name }}</p>
                                    </div>
                                    <div>
                                       <flux:button wire:click="logMeal({{ $meal->id }})" variant="primary">
                                            Hinzufügen
                                       </flux:button>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="py-4 text-center text-sm text-gray-500">
                                Keine Mahlzeiten gefunden. <a href="{{ route('pages.meals.index') }}" class="text-indigo-600 hover:underline" wire:navigate>Zeit, welche zu erstellen.</a>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flow-root border-t border-gray-200 pt-6 dark:border-gray-700">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
            {{ $date === today()->toDateString() ? 'Heutige Einträge' : 'Einträge vom ' . \Carbon\Carbon::parse($date)->format('d.m.Y') }}
        </h3>
        <ul role="list" class="mt-4 -my-5 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($todaysEntries as $entry)
                <li class="py-4" wire:key="entry-{{ $entry->id }}">
                    <div class="flex items-center space-x-2">
                        <button wire:click="toggleFavorite({{ $entry->food->id }})">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-6 w-6 {{ in_array($entry->food->id, $this->favoriteFoodIds) ? 'fill-yellow-400 text-yellow-400' : 'fill-gray-300 text-gray-300' }} transition-colors duration-200 hover:text-yellow-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-3.152a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                        </button>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $entry->food->name }}</p>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="number" 
                                    wire:model="entryQuantities.{{ $entry->id }}" 
                                    wire:keydown.enter="updateEntryQuantity({{ $entry->id }})" 
                                    wire:blur="updateEntryQuantity({{ $entry->id }})"
                                    class="w-20 rounded-md border-gray-300 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm transition-colors focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                >
                                <span class="text-sm text-gray-500">g - {{ $entry->consumed_at->format('H:i') }} Uhr</span>
                            </div>
                        </div>
                        <div class="text-right">
                           <p class="text-sm font-semibold text-indigo-600">{{ $entry->calories }} kcal</p>
                           <button wire:click="deleteEntry({{ $entry->id }})" class="text-xs text-red-500 hover:text-red-700">Löschen</button>
                        </div>
                    </div>
                </li>
            @empty
                <li class="py-4 text-center text-sm text-gray-500">
                    {{ $date === today()->toDateString() ? 'Bisher noch nichts erfasst. Bist du im Hungerstreik?' : 'An diesem Tag wurde nichts erfasst.' }}
                </li>
            @endforelse
        </ul>
    </div>
</div>