@props(['status'])

@php
    $styles = match ($status) {
        'Paid' => 'border-burgundy/20 bg-burgundy text-cream/95',
        'Partially Paid', 'Partial' => 'border-espresso/12 bg-espresso/[0.055] text-espresso/65',
        'Unpaid', 'Due' => 'border-wine/15 bg-wine/[0.07] text-wine',
        default => 'border-espresso/10 bg-espresso/[0.04] text-espresso/55',
    };

    $dot = match ($status) {
        'Paid' => 'bg-cream/75',
        'Partially Paid', 'Partial' => 'bg-espresso/35',
        'Unpaid', 'Due' => 'bg-wine',
        default => 'bg-espresso/30',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-medium tracking-[0.02em] {$styles}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
    {{ $status }}
</span>
