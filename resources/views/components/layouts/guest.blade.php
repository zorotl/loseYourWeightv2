<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <x-seo-meta />
    </head>
    <body class="min-h-screen bg-white font-sans text-gray-900 antialiased dark:bg-zinc-900 dark:text-gray-100">

        {{-- Simple Header --}}
        <header class="container mx-auto px-4 py-6">
            <nav class="flex items-center justify-between">
                <a href="{{ route('home') }}" wire:navigate>
                    <x-app-logo />
                </a>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" wire:navigate class="text-sm font-semibold hover:underline">Login</a>
                    <a href="{{ route('register') }}" wire:navigate class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Registrieren</a>
                </div>
            </nav>
        </header>

        {{-- Page Content --}}
        <main>
            {{ $slot }}
        </main>

        <footer class="bg-white py-8 dark:bg-zinc-800">
            @include('partials.footer')
        </footer>
        
        @include('cookie-consent::index')
        @fluxScripts
    </body>
</html>