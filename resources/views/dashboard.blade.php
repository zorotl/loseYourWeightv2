<x-layouts.app :title="__('Dashboard')">
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">

        {{-- Stat Card for Target Calories --}}
        <div class="relative flex flex-col gap-2 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <div class="text-sm font-medium text-zinc-500">
                {{ __('Dein tägliches Kalorienziel') }}
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold tracking-tight">
                    {{ auth()->user()->target_calories }}
                </span>
                <span class="text-lg font-medium text-zinc-500">
                    kcal
                </span>
            </div>
            <p class="text-xs text-zinc-500">
                Basiert auf einem Defizit von 500 kcal pro Tag.
            </p>
        </div>

        {{-- Stat Card for BMI --}}
        <div class="relative flex flex-col gap-2 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <div class="text-sm font-medium text-zinc-500">
                {{ __('Dein Body-Mass-Index (BMI)') }}
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold tracking-tight">
                    {{ auth()->user()->bmi }}
                </span>
            </div>
            <p class="text-xs text-zinc-500">
                Platzhalter-Gewicht: 80 kg. Ändern wir bald.
            </p>
        </div>
        
        {{-- Stat Card for Weight Goal --}}
        <div class="relative flex flex-col gap-2 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <div class="text-sm font-medium text-zinc-500">
                {{ __('Dein Gewichtsziel') }}
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold tracking-tight">
                    {{ auth()->user()->target_weight_kg }}
                </span>
                <span class="text-lg font-medium text-zinc-500">
                    kg
                </span>
            </div>
            <p class="text-xs text-zinc-500">
                Du schaffst das... vielleicht.
            </p>
        </div>

    </div>

    {{-- Platz für den eigentlichen Kalorienzähler --}}
    <div class="relative mt-4 h-96 flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="flex h-full items-center justify-center">
            <p class="text-zinc-500">Hier kommt bald der Kalorienzähler hin. Freu dich nicht zu früh.</p>
        </div>
    </div>
</x-layouts.app>