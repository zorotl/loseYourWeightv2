@props([
    'label' => '',
])

<div
    x-data="{
        value: @entangle($attributes->wire('model')),
        init() {
            flatpickr(this.$refs.input, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd.m.Y',
                defaultDate: this.value,
                locale: German,
                onChange: (selectedDates, dateStr, instance) => {
                    this.value = dateStr;
                }
            });
        }
    }"
>
    @if($label)
    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
    @endif
    <input
        x-ref="input"
        type="text"
        readonly
        {{ $attributes->whereDoesntStartWith('wire:model') }}
        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:ring-offset-zinc-800"
    >
</div>