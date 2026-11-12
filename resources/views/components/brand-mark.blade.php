@props(['class' => 'h-9 w-9'])

<img
    src="{{ asset('images/rentra-mark.png') }}"
    alt=""
    aria-hidden="true"
    draggable="false"
    {{ $attributes->merge(['class' => $class]) }}
>
