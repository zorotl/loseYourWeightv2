<div
    x-data="{
        show: false,
        message: '',
        type: 'success',
        timeout: null,
        showToast(event) {
            this.type = event.detail.type || 'success';
            this.message = event.detail.message || 'Erledigt.';
            this.show = true;

            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => this.show = false, 3000);
        }
    }"
    @show-toast.window="showToast($event)"
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="pointer-events-auto fixed top-5 right-5 z-50 w-full max-w-sm rounded-lg shadow-lg"
    :class="{
        'bg-green-500': type === 'success',
        'bg-red-500': type === 'error',
    }"
    x-cloak
>
    <div class="p-4">
        <p class="text-sm font-medium text-white" x-text="message"></p>
    </div>
</div>