@props([
    'property',
    'title',
    'eyebrow' => null,
    'description' => null,
    'maxWidth' => 'sm:max-w-xl',
])

<div
    x-data
    x-cloak
    x-show="$wire.{{ $property }}"
    x-on:keydown.escape.window="$wire.set('{{ $property }}', false)"
    class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $title }}"
>
    <div
        x-show="$wire.{{ $property }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-espresso/45 backdrop-blur-[3px]"
        x-on:click="$wire.set('{{ $property }}', false)"
    ></div>

    <div
        x-show="$wire.{{ $property }}"
        x-transition:enter="transition ease-[cubic-bezier(0.22,1,0.36,1)] duration-[240ms]"
        x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-4 sm:scale-[0.985]"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-4 sm:scale-[0.985]"
        class="relative flex max-h-[92dvh] w-full flex-col overflow-hidden rounded-t-[30px] border border-white/50 bg-parchment shadow-[0_-24px_80px_-24px_rgba(32,14,1,0.45)] sm:rounded-[30px] {{ $maxWidth }}"
    >
        <div class="mx-auto mt-2.5 h-1 w-10 shrink-0 rounded-full bg-espresso/15 sm:hidden"></div>

        <header class="flex shrink-0 items-start justify-between gap-4 px-6 pb-5 pt-5 sm:px-7 sm:pt-6">
            <div>
                @if ($eyebrow)
                    <p class="eyebrow">{{ $eyebrow }}</p>
                @endif
                <h2 class="mt-1.5 text-[22px] font-medium tracking-[-0.035em] text-espresso">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-1.5 max-w-sm text-[12.5px] leading-relaxed text-espresso/45">{{ $description }}</p>
                @endif
            </div>

            <button
                type="button"
                class="icon-btn -mr-1 -mt-1 shrink-0"
                x-on:click="$wire.set('{{ $property }}', false)"
                aria-label="Close dialog"
            >
                <x-icon name="x" class="h-4 w-4" />
            </button>
        </header>

        <div class="overflow-y-auto overscroll-contain px-6 pb-6 sm:px-7">
            {{ $slot }}
        </div>

        @if (isset($footer))
            <footer class="shrink-0 border-t border-espresso/8 bg-[#f2ebd2]/65 px-6 py-4 sm:px-7">
                {{ $footer }}
            </footer>
        @endif
    </div>
</div>
