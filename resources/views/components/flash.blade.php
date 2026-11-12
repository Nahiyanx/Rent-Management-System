@props(['message' => null])

@if ($message)
    <div
        x-data="{ visible: true }"
        x-init="setTimeout(() => visible = false, 4200)"
        x-show="visible"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="relative flex items-center gap-2.5 overflow-hidden rounded-2xl border border-burgundy/15 bg-burgundy/[0.055] px-4 py-3 text-[12px] text-espresso/65"
        role="status"
    >
        <x-icon name="circle-check" class="h-4 w-4 text-burgundy/70" />
        <span>{{ $message }}</span>
        <span class="flash-progress absolute inset-x-0 bottom-0 h-[2px] origin-left bg-burgundy/25"></span>
    </div>
@endif
