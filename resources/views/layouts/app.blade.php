@php
    $navItems = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
        ['route' => 'buildings', 'label' => 'Buildings', 'icon' => 'building-2'],
        ['route' => 'tenants', 'label' => 'Tenants', 'icon' => 'users-round'],
        ['route' => 'rent', 'label' => 'Rent', 'icon' => 'receipt-text'],
        ['route' => 'cashbook', 'label' => 'Cashbook', 'icon' => 'book-open'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
        <meta name="theme-color" content="#200e01">
        <meta name="description" content="Rentra is a calm, personal ledger for property finances.">

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('images/favicon-48x48.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">

        <title>{{ isset($title) ? $title.' · Rentra' : 'Rentra · Property Accounts' }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>

    <body class="min-h-dvh bg-cream text-espresso">
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[110] focus:rounded-full focus:bg-espresso focus:px-4 focus:py-2 focus:text-[12px] focus:font-medium focus:text-cream"
        >
            Skip to content
        </a>

        <div class="nav-progress" aria-hidden="true"></div>

        <div class="min-h-dvh">
            {{-- Desktop sidebar --}}
            <aside class="sidebar-surface fixed inset-y-0 left-0 z-40 hidden w-[276px] flex-col text-cream lg:flex">
                <div class="flex h-full flex-col px-5 py-7">
                    <a href="{{ route('dashboard') }}" wire:navigate class="group flex items-center gap-3 px-2">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-cream-50 ring-1 ring-cream/20 transition duration-200 group-hover:ring-cream/40">
                            <x-brand-mark class="h-7 w-7" />
                        </span>
                        <span>
                            <span class="block text-[19px] font-semibold tracking-[-0.045em] text-cream">Rentra</span>
                            <span class="mt-0.5 block text-[8.5px] uppercase tracking-[0.26em] text-cream/30">Property accounts</span>
                        </span>
                    </a>

                    <div class="mt-10 flex items-center justify-between px-3">
                        <span class="text-[9.5px] uppercase tracking-[0.24em] text-cream/25">Workspace</span>
                        <span class="rounded-full border border-cream/10 px-2 py-0.5 text-[8px] uppercase tracking-[0.16em] text-cream/30">Live</span>
                    </div>

                    <nav
                        x-data="sidebarNav"
                        class="relative mt-3 flex flex-col gap-1"
                        aria-label="Primary navigation"
                    >
                        <div
                            class="pointer-events-none absolute inset-x-0 rounded-2xl bg-cream/[0.07] ring-1 ring-inset ring-white/[0.055] transition-all duration-200 ease-[cubic-bezier(0.22,1,0.36,1)]"
                            :style="{
                                top: indicator.top + 'px',
                                height: indicator.height + 'px',
                                opacity: indicator.ready ? 1 : 0,
                            }"
                        ></div>

                        @foreach ($navItems as $item)
                            @php($active = request()->routeIs($item['route']))

                            <a
                                href="{{ route($item['route']) }}"
                                wire:navigate.hover
                                data-nav-active="{{ $active ? 'true' : 'false' }}"
                                @if ($active) aria-current="page" @endif
                                @class([
                                    'group relative z-10 flex items-center gap-3 rounded-2xl px-3.5 py-3 text-[13px] font-medium tracking-[-0.01em] transition-colors duration-200',
                                    'text-cream' => $active,
                                    'text-cream/50 hover:bg-cream/[0.035] hover:text-cream/85' => ! $active,
                                ])
                            >
                                @if ($active)
                                    <span class="absolute left-0 top-1/2 h-5 w-[2px] -translate-y-1/2 rounded-full bg-cream/80"></span>
                                @endif

                                <x-icon
                                    :name="$item['icon']"
                                    class="h-[18px] w-[18px]"
                                    :stroke-width="$active ? 2 : 1.7"
                                />

                                <span>{{ $item['label'] }}</span>

                                @if ($active)
                                    <span class="ml-auto h-1 w-1 rounded-full bg-cream/45"></span>
                                @endif
                            </a>
                        @endforeach
                    </nav>

                    <div class="mt-auto space-y-5">
                        <div class="rounded-2xl border border-white/[0.07] bg-white/[0.025] p-4">
                            <div class="flex items-center gap-2 text-[10px] text-cream/45">
                                <span class="h-1.5 w-1.5 rounded-full bg-cream/55"></span>
                                Local workspace
                            </div>
                            <p class="mt-2.5 text-[11px] leading-relaxed text-cream/30">
                                A quiet ledger for the homes you look after.
                            </p>
                        </div>

                        <div class="flex items-center justify-between px-1.5">
                            <div class="flex items-center gap-2.5">
                                <div class="grid h-8 w-8 place-items-center rounded-full border border-cream/[0.12] bg-cream/[0.06] text-[9px] font-medium tracking-[0.12em] text-cream/65">
                                    PA
                                </div>
                                <div>
                                    <p class="text-[11px] text-cream/65">Personal ledger</p>
                                    <p class="text-[9px] text-cream/25">Monthly ledger</p>
                                </div>
                            </div>

                            <x-icon name="sparkles" class="h-3.5 w-3.5 text-cream/20" />
                        </div>
                    </div>
                </div>
            </aside>

            <div class="lg:pl-[276px]">
                {{-- Compact mobile header --}}
                <header class="sticky top-0 z-40 border-b border-espresso/[0.07] bg-cream/80 backdrop-blur-2xl lg:hidden">
                    <div class="flex h-[60px] items-center justify-between px-5">
                        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2.5">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-cream-50 ring-1 ring-espresso/10">
                                <x-brand-mark class="h-6 w-6" />
                            </span>
                            <span class="text-[16px] font-semibold tracking-[-0.04em]">Rentra</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <span class="rounded-full border border-espresso/10 bg-[#fbf7e7]/60 px-2.5 py-1 text-[8.5px] font-medium uppercase tracking-[0.15em] text-espresso/40">
                                Demo
                            </span>
                            <div class="grid h-8 w-8 place-items-center rounded-full bg-espresso text-[9px] font-medium tracking-[0.1em] text-cream">
                                PA
                            </div>
                        </div>
                    </div>
                </header>

                {{-- Desktop utility bar --}}
                <div class="hidden items-center justify-between px-10 pt-7 lg:flex xl:px-14">
                    <div class="flex items-center gap-3">
                        <span class="h-1.5 w-1.5 rounded-full bg-wine/50"></span>
                        <p class="text-[9.5px] font-medium uppercase tracking-[0.24em] text-espresso/35">
                            Personal property ledger
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-espresso/10 bg-[#fbf7e7]/50 px-2.5 py-1 text-[8.5px] font-medium uppercase tracking-[0.16em] text-espresso/40">
                            <span class="h-1 w-1 rounded-full bg-wine/60"></span>
                            Demonstration data
                        </span>
                        <span class="text-[11px] text-espresso/35">{{ now()->format('D, j M Y') }}</span>
                        <div class="grid h-8 w-8 place-items-center rounded-full bg-espresso text-[9px] font-medium tracking-[0.1em] text-cream">
                            PA
                        </div>
                    </div>
                </div>

                <main
                    id="main-content"
                    wire:transition.navigate
                    class="mx-auto w-full max-w-[1440px] px-5 pb-32 pt-7 sm:px-8 lg:px-10 lg:pb-16 lg:pt-8 xl:px-14"
                >
                    {{ $slot }}
                </main>

                <footer class="mx-auto hidden w-full max-w-[1440px] items-center justify-between px-10 pb-8 text-[9px] uppercase tracking-[0.2em] text-espresso/25 lg:flex xl:px-14">
                    <span>Rentra · Property accounts</span>
                    <span>Cash · Bank · Monthly close</span>
                </footer>
            </div>

            {{-- Mobile bottom navigation --}}
            <nav
                class="mobile-nav-surface fixed inset-x-0 bottom-0 z-50 border-t border-espresso/[0.09] pb-[env(safe-area-inset-bottom)] shadow-[0_-10px_40px_-22px_rgba(32,14,1,0.35)] lg:hidden"
                aria-label="Primary navigation"
            >
                <div class="grid grid-cols-5 px-1.5 pt-1.5">
                    @foreach ($navItems as $item)
                        @php($active = request()->routeIs($item['route']))

                        <a
                            href="{{ route($item['route']) }}"
                            wire:navigate
                            @if ($active) aria-current="page" @endif
                            @class([
                                'group relative flex flex-col items-center gap-0.5 rounded-2xl px-1 pb-2 pt-1.5 text-[9px] font-medium tracking-[0.01em] transition-colors duration-200',
                                'text-wine' => $active,
                                'text-espresso/40' => ! $active,
                            ])
                        >
                            <span
                                @class([
                                    'absolute inset-x-4 top-0 h-[2px] rounded-full bg-wine transition-all duration-200',
                                    'scale-x-100 opacity-100' => $active,
                                    'scale-x-50 opacity-0' => ! $active,
                                ])
                            ></span>

                            <span
                                @class([
                                    'grid h-8 w-11 place-items-center rounded-xl transition-all duration-200',
                                    'bg-wine/[0.07] -translate-y-px' => $active,
                                    'group-hover:bg-espresso/[0.04]' => ! $active,
                                ])
                            >
                                <x-icon
                                    :name="$item['icon']"
                                    class="h-[18px] w-[18px]"
                                    :stroke-width="$active ? 2 : 1.7"
                                />
                            </span>

                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </nav>
        </div>

        @livewireScripts
    </body>
</html>
