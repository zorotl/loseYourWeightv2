<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-g">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        {{-- Simple Footer --}}
        <footer class="mt-12 bg-gray-50 py-8 dark:bg-zinc-800">
            <div class="container mx-auto px-4 text-center text-sm text-gray-500">
                <div class="flex justify-center space-x-6">
                    <a href="{{ route('terms') }}" wire:navigate class="hover:underline">AGB</a>
                    <a href="{{ route('privacy') }}" wire:navigate class="hover:underline">Datenschutz</a>
                    <a href="{{ route('imprint') }}" wire:navigate class="hover:underline">Impressum</a>
                </div>
                <p class="mt-4">&copy; {{ date('Y') }} {{ config('app.name') }}. Alle Rechte vorbehalten. Weil wir das so sagen.</p>
            </div>
        </footer>

    </body>
</html>