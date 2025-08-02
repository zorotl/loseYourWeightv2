<x-layouts.app :title="__('Profil einrichten')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex flex-col gap-1.5">
            <h1 class="text-2xl font-bold tracking-tight">
                {{ __('Vervollständige dein Profil') }}
            </h1>
            <p class="text-sm text-zinc-500">
                {{ __('Wir benötigen diese Angaben, um deine persönlichen Ziele genau zu berechnen.') }}
            </p>
        </div>

        {{-- Hier binden wir die separate Formular-Komponente ein --}}
        <livewire:components.setup-form />

    </div>
</x-layouts.app>