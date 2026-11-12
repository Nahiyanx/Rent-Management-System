@props(['class' => 'h-9 w-9'])

<svg
    {{ $attributes->merge(['class' => $class]) }}
    viewBox="0 0 40 40"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
>
    <path d="M8 32V19.4C8 13.1 13.4 8 20 8s12 5.1 12 11.4V32" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
    <path d="M14.5 32V20.2c0-3.2 2.5-5.8 5.5-5.8s5.5 2.6 5.5 5.8V32" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".55" />
    <path d="M6.5 32h27" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".7" />
    <circle cx="20" cy="5.25" r="1.25" fill="currentColor" opacity=".65" />
</svg>
