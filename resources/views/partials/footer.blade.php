{{-- Simple Footer --}}
<div class="container mx-auto px-4 text-center text-sm text-gray-500">
    <div class="flex justify-center space-x-6">
        <a href="{{ route('terms') }}" wire:navigate class="hover:underline">AGB</a>
        <a href="{{ route('privacy') }}" wire:navigate class="hover:underline">Datenschutz</a>
        <a href="{{ route('imprint') }}" wire:navigate class="hover:underline">Impressum</a>
    </div>
    <p class="mt-4">&copy; {{ date('Y') }} {{ config('app.name') }}. Alle Rechte vorbehalten.</p>
</div>

