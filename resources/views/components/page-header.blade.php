@props(['eyebrow' => null, 'title', 'description' => null])

<header class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
    <div class="max-w-2xl">
        @if ($eyebrow)
            <p class="eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-2 text-[34px] font-medium leading-[1.04] tracking-[-0.05em] text-espresso sm:text-[40px] lg:text-[44px]">
            {{ $title }}
        </h1>
        @if ($description)
            <p class="mt-3 max-w-xl text-[13px] leading-relaxed text-espresso/45 sm:text-sm">
                {{ $description }}
            </p>
        @endif
    </div>

    @if (trim($slot) !== '')
        <div class="flex shrink-0 flex-wrap items-center gap-2.5">
            {{ $slot }}
        </div>
    @endif
</header>
