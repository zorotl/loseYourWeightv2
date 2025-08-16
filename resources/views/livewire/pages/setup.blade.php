<x-layouts.app :title="__('Mein Profil')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        
        {{-- Title --}}
        <div class="flex flex-col gap-1.5">
            <h1 class="text-2xl font-bold tracking-tight">
                {{ __('Mein Profil & Ziele') }}
            </h1>
            <p class="text-sm text-zinc-500">
                {{ __('Hier kannst du deine persönlichen Daten und Ziele verwalten.') }}
            </p>
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            
            {{-- Left Side: Setup Form for personal data --}}
            <div>
                <livewire:components.setup-form />
            </div>
            
            {{-- Right Side: Stats and Add Weight Form --}}
            <div class="flex flex-col gap-6">
                {{-- Stats Display --}}
                <livewire:components.profile-stats />
                
                {{-- Add Weight Form --}}
                <livewire:components.add-weight-form />
            </div>
            
        </div>
    </div>
</x-layouts.app>