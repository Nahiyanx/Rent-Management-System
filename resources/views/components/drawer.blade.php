@props([
    'property',
    'title',
    'eyebrow' => null,
    'description' => null,
])

<div
    x-data
    x-cloak
    x-show="$wire.{{ $property }}"
    x-on:keydown.escape.window="$wire.set('{{ $property }}', false)"
    class="fixed inset-0 z-[70]"
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
        x-transition:enter="transition ease-[cubic-bezier(0.22,1,0.36,1)] duration-[260ms]"
        x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:translate-x-full"
        x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-[220ms]"
        x-transition:leave-start="opacity-100 translate-y-0 sm:translate-x-0"
        x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:translate-x-full"
        class="absolute inset-x-0 bottom-0 flex max-h-[92dvh] flex-col overflow-hidden rounded-t-[30px] border border-white/50 bg-parchment shadow-[0_-24px_80px_-24px_rgba(32,14,1,0.45)] sm:inset-y-0 sm:left-auto sm:right-0 sm:h-dvh sm:max-h-none sm:w-[min(520px,92vw)] sm:rounded-none sm:border-y-0 sm:border-r-0 sm:border-l sm:border-espresso/10 sm:shadow-[-30px_0_80px_-40px_rgba(32,14,1,0.5)]"
    >
        <div class="mx-auto mt-2.5 h-1 w-10 shrink-0 rounded-full bg-espresso/15 sm:hidden"></div>

        <header class="flex shrink-0 items-start justify-between gap-4 border-b border-espresso/8 px-6 pb-5 pt-5 sm:px-7 sm:pt-7">
            <div>
                @if ($eyebrow)
                    <p class="eyebrow">{{ $eyebrow }}</p>
                @endif
                <h2 class="mt-1.5 text-[22px] font-medium tracking-[-0.035em] text-espresso">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-1.5 text-[12.5px] leading-relaxed text-espresso/45">{{ $description }}</p>
                @endif
            </div>

            <button
                type="button"
                class="icon-btn -mr-1 -mt-1 shrink-0"
                x-on:click="$wire.set('{{ $property }}', false)"
                aria-label="Close panel"
            >
                <x-icon name="x" class="h-4 w-4" />
            </button>
        </header>

        <div class="flex-1 overflow-y-auto px-6 py-6 sm:px-7">
            {{ $slot }}
        </div>

        @if (isset($footer))
            <footer class="shrink-0 border-t border-espresso/8 bg-[#f2ebd2]/65 px-6 py-4 pb-[calc(1rem+env(safe-area-inset-bottom))] sm:px-7 sm:pb-5">
                {{ $footer }}
            </footer>
        @endif
    </div>
</div>
