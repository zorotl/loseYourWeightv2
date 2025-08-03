<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Passwort vergessen')" :description="__('Gib deine E-Mail ein, um einen Link zum Zurücksetzen des Passworts zu erhalten')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('E-Mail-Adresse')"
            type="email"
            required
            autofocus
            placeholder="email@beispiel.com"
        />

        <flux:button variant="primary" type="submit" class="w-full">{{ __('Link zum Zurücksetzen senden') }}</flux:button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        <span>{{ __('Oder zurück zu') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Anmeldung') }}</flux:link>
    </div>
</div>
