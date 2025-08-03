<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.app')]
class extends Component
{
    public Collection $users;

    public function mount(): void
    {
        $this->loadUsers();
    }

    public function deleteUser(int $userId): void
    {
        if ($userId === auth()->id()) {
            $this->dispatch('show-toast', message: 'Du kannst dich nicht selbst verbannen!', type: 'error');
            return;
        }
        
        $user = User::withTrashed()->findOrFail($userId);
        $user->delete();
        $this->loadUsers();
        $this->dispatch('show-toast', message: 'Benutzer wurde verbannt.');
    }

    /**
     * New method to restore a soft-deleted user.
     */
    public function restoreUser(int $userId): void
    {
        $user = User::withTrashed()->findOrFail($userId);
        $user->restore();
        $this->loadUsers();
        $this->dispatch('show-toast', message: 'Benutzer wurde wiederhergestellt.');
    }

    public function loadUsers(): void
    {
        $this->users = User::withTrashed()->latest()->get();
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <h1 class="text-2xl font-bold tracking-tight">Admin-Dashboard: Benutzerverwaltung</h1>

    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
            <thead class="bg-neutral-50 dark:bg-neutral-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-neutral-500">Aktionen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-900">
                @foreach($users as $user)
                    <tr class="{{ $user->trashed() ? 'bg-red-50/50 opacity-60 dark:bg-red-900/10' : '' }}">
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium {{ $user->trashed() ? 'text-neutral-500' : 'text-neutral-900 dark:text-white' }}">{{ $user->name }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm {{ $user->trashed() ? 'text-neutral-500' : 'text-neutral-500' }}">{{ $user->email }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500">
                            @if($user->trashed())
                                <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">Verbannt</span>
                            @else
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Aktiv</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            @if($user->trashed())
                                <button wire:click="restoreUser({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900">
                                    Wiederherstellen
                                </button>
                            @elseif($user->id !== auth()->id())
                                <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Soll dieser Benutzer wirklich verbannt werden?" class="text-red-600 hover:text-red-900">
                                    Verbannen
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>