<x-layouts.app :title="__('Dashboard')">
    {{-- Add Weight Form --}}
    <div class="mb-4">
        <livewire:components.add-weight-form />
    </div>

    {{-- Stats Overview --}}
    <livewire:components.stats-overview />

    {{-- Weight Chart --}}
    <div class="mt-4">
        <livewire:components.weight-chart />
    </div>
    
    {{-- Calorie Tracker --}}
    <div class="mt-4">
        <livewire:components.calorie-tracker />
    </div>
</x-layouts.app>