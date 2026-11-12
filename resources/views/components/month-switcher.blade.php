@props(['label'])

<div
    x-data="{ pickerOpen: false }"
    x-on:click.outside="pickerOpen = false"
    x-on:keydown.escape.window="pickerOpen = false"
    class="relative inline-flex items-center gap-0.5 rounded-full border border-espresso/10 bg-[#fbf7e7]/70 p-1 shadow-[0_1px_0_rgba(255,255,255,0.6)_inset] backdrop-blur-md"
>
    <button
        type="button"
        wire:click="previousMonth"
        wire:loading.attr="disabled"
        class="icon-btn h-8 w-8"
        aria-label="Previous month"
    >
        <x-icon name="chevron-left" class="h-4 w-4" />
    </button>

    <button
        type="button"
        x-on:click="pickerOpen = ! pickerOpen"
        class="min-w-[7.75rem] rounded-full px-1 py-1 text-center text-[12.5px] font-medium tracking-[-0.01em] text-espresso/80 transition-colors duration-200 hover:text-espresso"
        x-bind:aria-expanded="pickerOpen"
        aria-label="Choose month and year"
    >
        {{ $label }}
    </button>

    <button
        type="button"
        wire:click="nextMonth"
        wire:loading.attr="disabled"
        class="icon-btn h-8 w-8"
        aria-label="Next month"
    >
        <x-icon name="chevron-right" class="h-4 w-4" />
    </button>

    <div
        x-cloak
        x-show="pickerOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-[0.98]"
        class="absolute left-0 top-full z-40 mt-2 w-[min(17rem,calc(100vw-2.5rem))] rounded-3xl border border-espresso/[0.1] bg-parchment p-4 shadow-[0_24px_60px_-28px_rgba(32,14,1,0.55)] sm:left-1/2 sm:-translate-x-1/2"
    >
        <label for="month-picker-{{ md5($label) }}" class="label">Jump to month</label>
        <input
            id="month-picker-{{ md5($label) }}"
            type="month"
            wire:model.live="month"
            x-on:change="pickerOpen = false"
            class="input"
        >

        <p class="mt-2.5 text-[10px] leading-relaxed text-espresso/35">
            Pick any month to update the screen and both exports.
        </p>
    </div>
</div>
