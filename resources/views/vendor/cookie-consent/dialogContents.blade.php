<div class="js-cookie-consent cookie-consent fixed inset-x-0 bottom-0 z-50 p-4 sm:p-6">
    <div class="mx-auto max-w-md rounded-xl bg-white p-6 shadow-lg ring-1 ring-gray-900/10 dark:bg-zinc-800 dark:ring-white/10">
        <p class="text-sm text-gray-700 dark:text-gray-300">
            {!! trans('cookie-consent::texts.message') !!}
        </p>

        <div class="mt-4 flex items-center gap-x-5">
            <button
                class="js-cookie-consent-agree cookie-consent__agree w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
            >
                {{ trans('cookie-consent::texts.agree') }}
            </button>
        </div>
    </div>
</div>