<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                    <flux:navlist.item icon="rocket-launch" :href="route('pages.meals.index')" :current="request()->routeIs('pages.meals.index')" wire:navigate>{{ __('Mahlzeiten') }}</flux:navlist.item>
                    <flux:navlist.item icon="user-circle" :href="route('pages.setup')" :current="request()->routeIs('pages.setup')" wire:navigate>{{ __('Persönliche Ziele') }}</flux:navlist.item>                   
                    <flux:navlist.item icon="chart-pie" :href="route('statistics.index')" :current="request()->routeIs('statistics.index')" wire:navigate>{{ __('Statistik') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            {{-- ADMIN NAVIGATION (wird nur für Admins angezeigt) --}}
            @can('view-admin-panel')
                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="__('Administration')" class="grid">
                        <flux:navlist.item icon="users" :href="route('admin.index')" :current="request()->routeIs('admin.index')" wire:navigate>{{ __('Benutzer') }}</flux:navlist.item>
                        <flux:navlist.item icon="beaker" :href="route('admin.foods.index')" :current="request()->routeIs('admin.foods.index')" wire:navigate>{{ __('Lebensmittel') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan

            <flux:spacer />        

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group 
                        x-data="{
                            copied: false,
                            share() {
                                const shareData = {
                                    title: 'Lose Your Weight',
                                    text: 'Behalte deine Kalorien im Blick mit dieser App!',
                                    url: window.location.origin
                                };

                                if (navigator.share) {
                                    navigator.share(shareData).catch(console.error);
                                } else {
                                    navigator.clipboard.writeText(shareData.url).then(() => {
                                        this.copied = true;
                                        setTimeout(() => { this.copied = false }, 2000);
                                    });
                                }
                            }
                        }"
                    >
                        {{-- This item is shown when the link is copied --}}
                        <div x-show="copied" x-cloak>
                            <flux:menu.item icon="check-circle" class="text-green-500">
                                {{ __('Link kopiert!') }}
                            </flux:menu.item>
                        </div>

                        {{-- This is the actual share button, shown by default --}}
                        <div x-show="!copied">
                            <flux:menu.item as="button" @click="share()" icon="share" class="w-full text-left">
                                {{ __('Weiterempfehlen') }}
                            </flux:menu.item>
                        </div>

                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Einstellungen') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Abmelden') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group 
                        x-data="{
                            copied: false,
                            share() {
                                const shareData = {
                                    title: 'Lose Your Weight',
                                    text: 'Behalte deine Kalorien im Blick mit dieser App!',
                                    url: window.location.origin
                                };

                                if (navigator.share) {
                                    navigator.share(shareData).catch(console.error);
                                } else {
                                    navigator.clipboard.writeText(shareData.url).then(() => {
                                        this.copied = true;
                                        setTimeout(() => { this.copied = false }, 2000);
                                    });
                                }
                            }
                        }"
                    >
                        {{-- This item is shown when the link is copied --}}
                        <div x-show="copied" x-cloak>
                            <flux:menu.item icon="check-circle" class="text-green-500">
                                {{ __('Link kopiert!') }}
                            </flux:menu.item>
                        </div>

                        {{-- This is the actual share button, shown by default --}}
                        <div x-show="!copied">
                            <flux:menu.item as="button" @click="share()" icon="share" class="w-full text-left">
                                {{ __('Weiterempfehlen') }}
                            </flux:menu.item>
                        </div>

                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Einstellungen') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Abmelden') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <flux:main class="flex flex-col min-h-screen">
            <div class="flex-1">
                {{ $slot }}
            </div>

            <footer class="bg-white py-8 dark:bg-zinc-800 mt-auto">
                @include('partials.footer')
            </footer>
        </flux:main>

        @fluxScripts
        <x-toast />
    </body>
</html>
