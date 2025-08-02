<?php

use App\Models\Food;
use App\Models\FoodLogEntry;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Collection $todaysEntries;

    #[Rule('required|string|max:100')]
    public string $foodName = '';

    #[Rule('required|integer|min:0|max:5000')]
    public int|string $caloriesPer100g = '';
    
    #[Rule('required|integer|min:1|max:5000')]
    public int|string $quantity = '';

    public function mount(): void
    {
        $this->loadEntries();
    }

    public function logFood(): void
    {
        $validated = $this->validate();

        // Find or create the food item in our new foods table.
        // This is the core of our "manual entry" logic.
        $food = Food::firstOrCreate(
            [
                'name' => $validated['foodName'],
                'creator_id' => auth()->id(),
                'source' => 'user',
            ],
            [
                'calories' => $validated['caloriesPer100g'],
            ]
        );

        // Now create the log entry and link it to the food.
        auth()->user()->foodLogEntries()->create([
            'food_id' => $food->id,
            'quantity_grams' => $validated['quantity'],
            'calories' => round(($food->calories / 100) * $validated['quantity']),
            'consumed_at' => now(),
        ]);
        
        $this->reset('foodName', 'caloriesPer100g', 'quantity');
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
    {{-- Header und Fortschrittsbalken --}}
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

    {{-- Formular für neue Einträge --}}
     <form wire:submit="logFood" class="mt-6 flex items-end gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
        <div class="flex-1">
            <flux:input wire:model="foodName" :label="__('Lebensmittel')" required />
        </div>
        <div class="w-48">
            <flux:input wire:model="caloriesPer100g" :label="__('Kalorien pro 100g')" type="number" required />
        </div>
        <div class="w-32">
            <flux:input wire:model="quantity" :label="__('Menge (g)')" type="number" required />
        </div>
        <div>
            <flux:button type="submit" variant="primary">
                {{ __('Hinzufügen') }}
            </flux:button>
        </div>
    </form>

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