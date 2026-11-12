@props(['amount' => 0, 'symbol' => '৳'])

@php
    $value = (float) $amount;
    $formatted = floor($value) === $value
        ? number_format($value)
        : number_format($value, 2);
@endphp

<span {{ $attributes->merge(['class' => 'tabular-nums']) }}>{{ $symbol }}{{ $formatted }}</span>
