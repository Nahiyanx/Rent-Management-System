@props(['name', 'size' => 20, 'strokeWidth' => 1.75, 'label' => null])

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="{{ $strokeWidth }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif
    {{ $attributes->merge(['class' => 'shrink-0']) }}
>
    {!! \App\Support\Icon::paths($name) !!}
</svg>
