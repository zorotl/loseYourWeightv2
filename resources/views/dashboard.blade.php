<x-layouts.app :title="__('Dashboard')">
    <div class="mb-4">
        <livewire:components.add-weight-form />
    </div>

    {{-- Der alte grid-div wird durch diese eine Zeile ersetzt --}}
    <livewire:components.stats-overview />
    
    <div class="relative mt-4 h-96 flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="flex h-full items-center justify-center">
            <p class="text-zinc-500">Hier kommt bald der Kalorienzähler hin. Freu dich nicht zu früh.</p>
        </div>
    </div>
</x-layouts.app>