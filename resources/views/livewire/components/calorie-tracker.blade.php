<?php

use App\Models\Food;
use App\Models\FoodLogEntry;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Collection $todaysEntries;

    // Search properties
    public string $search = '';
    public array $searchResults = [];
    public ?array $selectedFood = null;

    // Logging properties
    #[Rule('required|integer|min:1|max:5000')]
    public int|string $quantity = '';

    // Manual entry properties
    #[Rule('required|string|max:100')]
    public string $manualFoodName = '';
    #[Rule('required|integer|min:0|max:5000')]
    public int|string $manualCaloriesPer100g = '';
    public bool $showManualForm = false;

    public function mount(): void
    {
        $this->loadEntries();
    }

    // This method is triggered automatically when the $search property is updated.
    public function updatedSearch(string $value): void
    {
        // Reset if search is too short
        if (strlen($value) < 3) {
            $this->searchResults = [];
            $this->selectedFood = null;
            return;
        }

        // Fetch data from OpenFoodFacts API
        $response = Http::get('https://world.openfoodfacts.org/cgi/search.pl', [
            'search_terms' => $value,
            'search_simple' => 1,
            'action' => 'process',
            'json' => 1,
            'page_size' => 10, // Limit results
        ]);

        if ($response->ok()) {
            $this->searchResults = $response->json()['products'] ?? [];
        } else {
            // Handle API errors gracefully, maybe log them later
            $this->searchResults = [];
        }
    }
    
    // Called when a user clicks a search result.
    public function selectFood(string $productCode): void
    {
        // Find the full product details in our search results
        $productData = collect($this->searchResults)->firstWhere('code', $productCode);

        if (!$productData) return;

        // We only care about products with calorie information
        $calories = data_get($productData, 'nutriments.energy-kcal_100g');
        if (!$calories) {
            // Here you could add an error message to the user
            return;
        }

        // Prepare the selected food data for logging
        $this->selectedFood = [
            'source' => 'openfoodfacts',
            'source_id' => $productData['code'],
            'name' => data_get($productData, 'product_name', 'Unknown'),
            'brand' => data_get($productData, 'brands', 'Unknown'),
            'calories' => (int) $calories,
        ];
        
        // Clear search results to hide the list
        $this->searchResults = [];
        $this->search = $this->selectedFood['name'];
    }

    // Called when the user clicks the final "Add" button.
    public function logSelectedFood(): void
    {
        $this->validateOnly('quantity');
        
        if (!$this->selectedFood) return;

        // Find or create the food item in our local DB (the hybrid model)
        $food = Food::firstOrCreate(
            [
                'source' => $this->selectedFood['source'],
                'source_id' => $this->selectedFood['source_id'],
            ],
            [
                'name' => $this->selectedFood['name'],
                'brand' => $this->selectedFood['brand'],
                'calories' => $this->selectedFood['calories'],
            ]
        );

        // Create the log entry
        auth()->user()->foodLogEntries()->create([
            'food_id' => $food->id,
            'quantity_grams' => $this->quantity,
            'calories' => round(($food->calories / 100) * $this->quantity),
            'consumed_at' => now(),
        ]);

        $this->reset('selectedFood', 'quantity', 'search');
        $this->loadEntries();
    }
    
    // The old logFood method, repurposed for manual entry.
    public function logManualFood(): void
    {
        $validated = $this->validate([
            'manualFoodName' => 'required|string|max:100',
            'manualCaloriesPer100g' => 'required|integer|min:0|max:5000',
            'quantity' => 'required|integer|min:1|max:5000',
        ]);
        
        $food = Food::firstOrCreate(
            ['name' => $validated['manualFoodName'], 'creator_id' => auth()->id(), 'source' => 'user'],
            ['calories' => $validated['manualCaloriesPer100g']]
        );

        auth()->user()->foodLogEntries()->create([
            'food_id' => $food->id,
            'quantity_grams' => $validated['quantity'],
            'calories' => round(($food->calories / 100) * $validated['quantity']),
            'consumed_at' => now(),
        ]);

        $this->reset('manualFoodName', 'manualCaloriesPer100g', 'quantity');
        $this->showManualForm = false;
        $this->loadEntries();
    }
    
    public function deleteEntry(int $entryId): void
    {
        // Find the entry ensuring it belongs to the logged-in user. No cheating.
        $entry = FoodLogEntry::where('id', $entryId)->where('user_id', auth()->id())->first();
        
        if ($entry) {
            $entry->delete();
            $this->loadEntries();
        }
    }

    public function loadEntries(): void
    {
        $this->todaysEntries = FoodLogEntry::with('food') // Eager load the food data
            ->where('user_id', auth()->id())
            ->whereDate('consumed_at', today())
            ->orderBy('consumed_at', 'desc')
            ->get();
    }

    #[Computed]
    public function consumedCalories(): int
    {
        return $this->todaysEntries->sum('calories');
    }

    #[Computed]
    public function remainingCalories(): int
    {
        return auth()->user()->target_calories - $this->consumedCalories();
    }

    #[Computed]
    public function caloriesPercentage(): int
    {
        $target = auth()->user()->target_calories;
        return $target > 0 ? min(100, round(($this->consumedCalories() / $target) * 100)) : 0;
    }

}; ?>

<div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
    {{-- Header and Progress Bar remain the same --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Heutige Kalorien</h3>
            <p class="mt-1 text-sm text-gray-500">
                Verbraucht: <span class="font-bold">{{ $this->consumedCalories() }}</span> / {{ auth()->user()->target_calories }} kcal
            </p>
        </div>
        <div class="w-full md:w-1/3">
            <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                <div class="h-2.5 rounded-full bg-indigo-600" style="width: {{ $this->caloriesPercentage() }}%"></div>
            </div>
        </div>
    </div>

    {{-- Main logging section --}}
    <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
        {{-- Search Input and Results --}}
        <div class="relative">
            <flux:input wire:model.live.debounce.500ms="search" :label="__('Lebensmittel suchen...')" placeholder="z.B. Cola Zero" />

            @if(!empty($searchResults))
                <div class="absolute z-10 mt-1 w-full rounded-md border border-gray-300 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800">
                    <ul class="max-h-60 overflow-auto rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                        @foreach($searchResults as $product)
                            <li wire:click="selectFood('{{ $product['code'] }}')" class="relative cursor-pointer select-none py-2 px-4 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                                <span class="block truncate">{{ data_get($product, 'product_name', 'Unbekannt') }}</span>
                                <span class="block truncate text-xs text-gray-500">{{ data_get($product, 'brands', 'Unbekannte Marke') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Form to log a SELECTED food --}}
        @if($selectedFood)
            <form wire:submit="logSelectedFood" class="mt-4 flex items-end gap-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
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
        
        {{-- Toggle for manual form --}}
        <div class="mt-4 text-center">
            <button wire:click="$toggle('showManualForm')" class="text-sm text-indigo-600 hover:underline">
                {{ $showManualForm ? 'Suche verwenden' : 'Produkt nicht gefunden? Manuell eintragen.' }}
            </button>
        </div>

        {{-- Manual Entry Form (hidden by default) --}}
        @if($showManualForm)
            <form wire:submit="logManualFood" class="mt-4 flex items-end gap-4 rounded-lg border border-dashed border-gray-400 p-4">
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

    {{-- Liste der heutigen Einträge --}}
    <div class="mt-6 flow-root">
        <ul role="list" class="-my-5 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($this->todaysEntries as $entry)
                <li class="py-4" wire:key="{{ $entry->id }}">
                    <div class="flex items-center space-x-4">
                        <div class="min-w-0 flex-1">
                            {{-- Greift jetzt auf den Namen des verknüpften Lebensmittels zu --}}
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $entry->food->name }}</p>
                            <p class="truncate text-sm text-gray-500">{{ $entry->quantity_grams }}g - {{ $entry->consumed_at->format('H:i') }} Uhr</p>
                        </div>
                        <div class="text-right">
                           <p class="text-sm font-semibold text-indigo-600">{{ $entry->calories }} kcal</p>
                           <button wire:click="deleteEntry({{ $entry->id }})" class="text-xs text-red-500 hover:text-red-700">Löschen</button>
                        </div>
                    </div>
                </li>
            @empty
                <li class="py-4 text-center text-sm text-gray-500">
                    Bisher noch nichts protokolliert. Ein vorbildlicher Asket oder nur vergesslich?
                </li>
            @endforelse
        </ul>
    </div>
</div>