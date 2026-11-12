@props(['label'])

<div class="inline-flex items-center gap-0.5 rounded-full border border-espresso/10 bg-[#fbf7e7]/70 p-1 shadow-[0_1px_0_rgba(255,255,255,0.6)_inset] backdrop-blur-md">
    <button
        type="button"
        wire:click="previousMonth"
        wire:loading.attr="disabled"
        class="icon-btn h-8 w-8"
        aria-label="Previous month"
    >
        <x-icon name="chevron-left" class="h-4 w-4" />
    </button>

    <span class="min-w-[7.75rem] px-1 text-center text-[12.5px] font-medium tracking-[-0.01em] text-espresso/80">
        {{ $label }}
    </span>

    <button
        type="button"
        wire:click="nextMonth"
        wire:loading.attr="disabled"
        class="icon-btn h-8 w-8"
        aria-label="Next month"
    >
        <x-icon name="chevron-right" class="h-4 w-4" />
    </button>
</div>
