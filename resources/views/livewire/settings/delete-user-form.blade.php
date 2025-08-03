<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Konto löschen') }}</flux:heading>
        <flux:subheading>{{ __('Lösche dein Konto und alle dazugehörigen Daten.') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('Konto löschen') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Bist du sicher, dass du dein Konto löschen möchtest?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Wenn du dein Konto löschst, werden alle Daten dauerhaft entfernt. Gib dein Passwort ein, um die Löschung zu bestätigen.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('Passwort')" type="password" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Abbrechen') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Konto löschen') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
