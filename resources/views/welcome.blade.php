<x-layouts.guest>
    <div class="space-y-24 md:space-y-32">

        {{-- 1. Hero Section --}}
        <section class="text-center">
            <div class="container mx-auto px-4 py-16 sm:py-24">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                    Kalorienzählen ohne Kopfschmerzen.
                    <span class="bg-gradient-to-r from-teal-400 to-blue-500 bg-clip-text text-transparent">Und ohne Kosten.</span>
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600 dark:text-gray-400">
                    Alle Kernfunktionen, die du zum Abnehmen brauchst. Keine Abofallen, keine Premium-Features, keine Ausreden mehr. Für immer kostenlos.
                </p>
                <div class="mt-10">
                    <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-8 py-3 text-lg font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Jetzt kostenlos starten
                    </a>
                </div>
            </div>
        </section>

        {{-- 2. Features Section --}}
        <section class="container mx-auto px-4">
            <div class="mx-auto max-w-xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Alles was du brauchst. Nichts was du nicht brauchst.</h2>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    Wir konzentrieren uns auf die Werkzeuge, die wirklich einen Unterschied machen.
                </p>
            </div>
            <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                {{-- Feature 1 --}}
                <div class="flex flex-col items-center gap-y-4 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.036.243c-2.132 0-4.14-.352-6.014-.957l-1.321-.442m-2.219-4.435a28.2 28.2 0 00-4.212-2.219l-1.321-.442a2.31 2.31 0 01-1.424-2.152l.21-8.525c.045-.18.303-.18.348 0l.21 8.525a2.31 2.31 0 01-1.424 2.152l-1.321.442a28.2 28.2 0 00-4.212 2.219m2.219-4.435l-1.321.442m6.75-4.97a48.416 48.416 0 01-13.5 0" /></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Intelligenter Kalorienrechner</h3>
                    <p class="text-gray-600 dark:text-gray-400">Basierend auf deinen Zielen, deinem Körper und deinem Alltag berechnen wir dein persönliches Kalorienbudget.</p>
                </div>
                {{-- Feature 2 --}}
                <div class="flex flex-col items-center gap-y-4 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 3a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m-1.5-3l1.19-4.456a2.25 2.25 0 012.14-1.634h10.342a2.25 2.25 0 012.14 1.634L22.5 9" /></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Mahlzeiten & Lebensmittel-Suche</h3>
                    <p class="text-gray-600 dark:text-gray-400">Finde Lebensmittel in einer riesigen Online-Datenbank oder erstelle eigene Mahlzeiten, um sie mit einem Klick zu loggen.</p>
                </div>
                {{-- Feature 3 --}}
                <div class="flex flex-col items-center gap-y-4 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Visueller Fortschritt</h3>
                    <p class="text-gray-600 dark:text-gray-400">Verfolge deine Gewichtsentwicklung auf einem klaren Chart und bleibe motiviert, indem du deine Erfolge siehst.</p>
                </div>
            </div>
        </section>

        {{-- 3. How it works Section --}}
        <section class="container mx-auto px-4">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Drei einfache Schritte zum Erfolg</h2>
            </div>
            <div class="mt-16 grid grid-cols-1 gap-12 md:grid-cols-3">
                {{-- Step 1 --}}
                <div class="flex flex-col items-center text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-teal-500 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">1. Ziele setzen</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Registriere dich kostenlos und gib deine Körperdaten sowie dein Wunschgewicht und Zieldatum ein.</p>
                </div>
                {{-- Step 2 --}}
                <div class="flex flex-col items-center text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-teal-500 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">2. Essen tracken</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Nutze die Lebensmittel-Suche oder erstelle eigene Mahlzeiten, um deine täglichen Kalorien zu protokollieren.</p>
                </div>
                {{-- Step 3 --}}
                <div class="flex flex-col items-center text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-teal-500 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">3. Erfolge sehen</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Trage regelmässig dein Gewicht ein und verfolge deinen Fortschritt auf dem Weg zu deinem Ziel.</p>
                </div>
            </div>
        </section>

        {{-- 4. FAQ Section --}}
        <section class="container mx-auto px-4">
            <div class="mx-auto max-w-3xl">
                <h2 class="text-3xl font-bold tracking-tight text-center text-gray-900 dark:text-white sm:text-4xl">Häufig gestellte Fragen</h2>
                <div class="mt-10 space-y-4" x-data="{ open: null }">
                    {{-- FAQ 1 --}}
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                        <button x-on:click="open = open === 1 ? null : 1" class="flex w-full items-center justify-between p-6 text-left">
                            <span class="font-semibold text-gray-900 dark:text-white">Ist die App wirklich komplett kostenlos?</span>
                            <svg class="h-6 w-6 transform text-gray-400 transition-transform" :class="{'rotate-180': open === 1}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open === 1" x-cloak class="px-6 pb-6 text-gray-600 dark:text-gray-400 prose prose-invert max-w-none">
                            <p>Ja. Alle Kernfunktionen sind und bleiben kostenlos. Wir planen, eventuell in ferner Zukunft dezente Werbung zu schalten, um die Serverkosten zu decken. Es wird aber keine Premium-Version oder Abofallen geben.</p>
                        </div>
                    </div>
                    {{-- FAQ 2 --}}
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                        <button x-on:click="open = open === 2 ? null : 2" class="flex w-full items-center justify-between p-6 text-left">
                            <span class="font-semibold text-gray-900 dark:text-white">Werden meine Daten verkauft?</span>
                            <svg class="h-6 w-6 transform text-gray-400 transition-transform" :class="{'rotate-180': open === 2}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open === 2" x-cloak class="px-6 pb-6 text-gray-600 dark:text-gray-400 prose prose-invert max-w-none">
                            <p>Nein. Deine persönlichen Daten gehören dir. Wir werden sie niemals an Dritte verkaufen. Details findest du in unserer Datenschutzerklärung.</p>
                        </div>
                    </div>
                    {{-- FAQ 3 --}}
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                        <button x-on:click="open = open === 3 ? null : 3" class="flex w-full items-center justify-between p-6 text-left">
                            <span class="font-semibold text-gray-900 dark:text-white">Woher kommen die Lebensmitteldaten?</span>
                            <svg class="h-6 w-6 transform text-gray-400 transition-transform" :class="{'rotate-180': open === 3}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open === 3" x-cloak class="px-6 pb-6 text-gray-600 dark:text-gray-400 prose prose-invert max-w-none">
                            <p>Wir nutzen die riesige, von der Community gepflegte Datenbank von OpenFoodFacts. Zusätzlich kannst du eigene Lebensmittel manuell eintragen, falls du etwas nicht findest.</p>
                        </div>
                    </div>
                    {{-- FAQ 4 --}}
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                        <button x-on:click="open = open === 4 ? null : 4" class="flex w-full items-center justify-between p-6 text-left">
                            <span class="font-semibold text-gray-900 dark:text-white">Kann ich auch Sport und Aktivitäten tracken?</span>
                            <svg class="h-6 w-6 transform text-gray-400 transition-transform" :class="{'rotate-180': open === 4}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open === 4" x-cloak class="px-6 pb-6 text-gray-600 dark:text-gray-400 prose prose-invert max-w-none">
                            <p>Momentan nicht. `Lose Your Weight` konzentriert sich voll auf die Ernährungsseite der Gleichung ("Calories In"). Das macht die App einfach und fokussiert. Eine Funktion für "Calories Out" ist aber eine mögliche Idee für die Zukunft.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</x-layouts.guest>