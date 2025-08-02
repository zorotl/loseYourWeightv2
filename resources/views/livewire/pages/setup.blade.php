<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    #[Rule('required|integer|min:100|max:250')]
    public ?int $height_cm = null;

    #[Rule('required|date|before:today')]
    public ?string $date_of_birth = null;

    #[Rule('required|in:male,female')]
    public ?string $gender = null;

    #[Rule('required|integer|between:1,5')]
    public ?int $activity_level = null;

    #[Rule('required|numeric|min:30|max:200')]
    public ?float $target_weight_kg = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->height_cm = $user->height_cm;
        $this->date_of_birth = $user->date_of_birth;
        $this->gender = $user->gender;
        $this->activity_level = $user->activity_level;
        $this->target_weight_kg = $user->target_weight_kg;
    }

    public function save()
    {
        $validated = $this->validate();
        Auth::user()->update($validated);
        $this->redirect('/dashboard', navigate: true);
    }
}; ?>

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

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <form wire:submit="save" class="flex flex-col gap-6">
                <flux:input
                    wire:model="height_cm"
                    :label="__('Grösse (in cm)')"
                    type="number"
                    required
                />

                <flux:input
                    wire:model="date_of_birth"
                    :label="__('Geburtsdatum')"
                    type="date"
                    required
                />

                <flux:select
                    wire:model="gender"
                    :label="__('Geschlecht')"
                    :placeholder="__('Bitte wählen...')"
                    required
                >
                    <option value="male">{{ __('Männlich') }}</option>
                    <option value="female">{{ __('Weiblich') }}</option>
                </flux:select>

                <flux:select
                    wire:model="activity_level"
                    :label="__('Aktivitätslevel')"
                    :placeholder="__('Bitte wählen...')"
                    required
                >
                    <option value="1">{{ __('Sitzend (Bürojob)') }}</option>
                    <option value="2">{{ __('Leicht aktiv (leichte Bewegung)') }}</option>
                    <option value="3">{{ __('Mässig aktiv (moderate Bewegung)') }}</option>
                    <option value="4">{{ __('Sehr aktiv (körperlich anstrengender Job)') }}</option>
                    <option value="5">{{ __('Extrem aktiv (Leistungssport)') }}</option>
                </flux:select>

                <flux:input
                    wire:model="target_weight_kg"
                    :label="__('Zielgewicht (in kg)')"
                    type="number"
                    step="0.1"
                    required
                />

                <div class="pt-2">
                    <flux:button type="submit" variant="primary" class="w-full">
                        {{ __('Speichern und Starten') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>