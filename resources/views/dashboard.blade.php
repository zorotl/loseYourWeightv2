<x-layouts.app :title="__('Dashboard')">
    {{-- Grid for forms. On small screens 1 column, on large (lg) screens 2 columns --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <livewire:components.add-weight-form />
        <livewire:components.update-goal-form />
    </div>

    {{-- Stats overview --}}
    <div class="mt-6">
        <livewire:components.stats-overview />
    </div>
    
    {{-- Calorie Tracker --}}
    <div class="mt-6">
        <livewire:components.calorie-tracker />
    </div>
</x-layouts.app>