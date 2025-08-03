<?php

use App\Models\Food;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('components.layouts.app')]
class extends Component
{
    use WithPagination;

    public bool $showEditModal = false;
    public ?Food $editingFood = null;

    #[Rule('required|string|max:100')]
    public string $name = '';
    #[Rule('nullable|string|max:100')]
    public string $brand = '';
    #[Rule('required|integer|min:0')]
    public int $calories = 0;

    public function mount(): void
    {
        Gate::authorize('view-admin-panel');
    }

    public function editFood(int $foodId): void
    {
        $this->editingFood = Food::findOrFail($foodId);
        $this->name = $this->editingFood->name;
        $this->brand = $this->editingFood->brand;
        $this->calories = $this->editingFood->calories;
        $this->showEditModal = true;
    }

    public function saveFood(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'calories' => 'required|integer|min:0',
        ]);
        
        if ($this->editingFood) {
            $this->editingFood->update($validated);
            $this->dispatch('show-toast', message: 'Lebensmittel aktualisiert.');
            $this->closeModal();
        }
    }
    
    public function closeModal(): void
    {
        $this->showEditModal = false;
        $this->reset('editingFood', 'name', 'brand', 'calories');
    }

    public function deleteFood(int $foodId): void
    {
        $food = Food::findOrFail($foodId);
        $food->delete();
        $this->dispatch('show-toast', message: 'Lebensmittel wurde gelöscht.');
    }

    public function with(): array
    {
        return [
            'foods' => Food::with('creator')->latest()->paginate(15),
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <h1 class="text-2xl font-bold tracking-tight">Admin: Lebensmittel-Verwaltung</h1>

    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
            <thead class="bg-neutral-50 dark:bg-neutral-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Name / Marke</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Kalorien / 100g</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Quelle</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-neutral-500">Aktionen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-900">
                @foreach($foods as $food)
                    <tr wire:key="{{ $food->id }}">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $food->name }}</div>
                            <div class="text-sm text-neutral-500">{{ $food->brand }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500">{{ $food->calories }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500">
                            @if($food->source === 'user')
                                Manuell ({{ $food->creator->name ?? 'N/A' }})
                            @else
                                OpenFoodFacts
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium space-x-4">
                            <button wire:click="editFood({{ $food->id }})" class="text-indigo-600 hover:text-indigo-900">
                                Bearbeiten
                            </button>
                            <button wire:click="deleteFood({{ $food->id }})" wire:confirm="Dieses Lebensmittel wirklich endgültig löschen?" class="text-red-600 hover:text-red-900">
                                Löschen
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $foods->links() }}
    </div>

    {{-- Edit Modal --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-data @keydown.escape.window="$wire.closeModal()">
            <div @click.away="$wire.closeModal()" class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-lg dark:bg-neutral-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Lebensmittel bearbeiten</h3>
                <form wire:submit.prevent="saveFood" class="mt-4 space-y-4">
                    <div>
                        <flux:input wire:model="name" :label="__('Name')" required />
                    </div>
                    <div>
                        <flux:input wire:model="brand" :label="__('Marke')" />
                    </div>
                    <div>
                        <flux:input wire:model="calories" type="number" :label="__('Kalorien pro 100g')" required />
                    </div>
                    <div class="flex justify-end space-x-4 pt-4">
                        <flux:button type="button" variant="outline" @click="$wire.closeModal()">Abbrechen</flux:button>
                        <flux:button type="submit" variant="primary">Speichern</flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>