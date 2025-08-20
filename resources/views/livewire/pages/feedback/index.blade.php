<?php

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.app')]
class extends Component
{
    #[Rule('required|in:1,2,3')]
    public int $type = 1; // Default to "Allgemeines Feedback"

    #[Rule('required|string|min:10|max:5000')]
    public string $message = '';

    public function saveFeedback(): void
    {
        $this->validate();

        $types = [
            1 => 'Allgemeines Feedback',
            2 => 'Feature-Wunsch',
            3 => 'Bug-Report',
        ];

        auth()->user()->feedback()->create([
            'type' => $types[$this->type],
            'priority' => $this->type,
            'message' => $this->message,
            'status' => 'neu',
            'url_at_submission' => url()->previous(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->reset('message');
        $this->dispatch('show-toast', message: 'Vielen Dank für dein Feedback!');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <div class="flex flex-col gap-1.5">
        <h1 class="text-2xl font-bold tracking-tight">
            {{ __('Feedback geben') }}
        </h1>
        <p class="text-sm text-zinc-500">
            {{ __('Hast du einen Fehler gefunden oder eine Idee für ein neues Feature? Lass es uns wissen!') }}
        </p>
    </div>

    <div class="max-w-2xl rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <form wire:submit="saveFeedback" class="space-y-6">
            <flux:select
                wire:model="type"
                :label="__('Art des Feedbacks')"
                required
            >
                <option value="3">Fehler melden (Bug-Report)</option>
                <option value="2">Idee / Wunsch (Feature-Request)</option>
                <option value="1">Allgemeines Feedback</option>
            </flux:select>

            <flux:textarea
                wire:model="message"
                :label="__('Deine Nachricht')"
                :placeholder="__('Bitte beschreibe dein Anliegen so detailliert wie möglich...')"
                rows="8"
                required
            />

            <div>
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Feedback senden') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>