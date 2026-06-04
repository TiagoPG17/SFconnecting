<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="api-token" content="{{ session('api_token', '') }}">
    @endauth
    <title>{{ $title ?? 'SFconnecting CRM' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full font-sans antialiased" x-data>

<div class="flex h-full">

    {{-- Sidebar --}}
    <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-slate-900">
        {{-- Logo --}}
        <div class="flex h-16 items-center gap-3 px-6 border-b border-slate-800">
            <div class="w-8 h-8 rounded-lg bg-blue-500 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-white font-semibold text-sm tracking-wide">SFconnecting</span>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <x-ui.nav-item href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="home">
                Dashboard
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('dash.vendedor') }}" :active="request()->routeIs('dash.vendedor')" icon="trending-up">
                Mi desempeño
            </x-ui.nav-item>
            @role('admin|gerente')
            <x-ui.nav-item href="{{ route('dash.gerencial') }}" :active="request()->routeIs('dash.gerencial')" icon="bar-chart">
                Visión gerencial
            </x-ui.nav-item>
            @endrole

            <div class="pt-4 pb-1 px-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Comercial</p>
            </div>

            <x-ui.nav-item href="{{ route('prospectos.index') }}" :active="request()->routeIs('prospectos.*')" icon="user-plus">
                Prospectos
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('negocios.index') }}" :active="request()->routeIs('negocios.*')" icon="briefcase">
                Negocios
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('negocios.kanban') }}" :active="request()->routeIs('negocios.kanban')" icon="layout">
                Pipeline Kanban
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('forecast.index') }}" :active="request()->routeIs('forecast.*')" icon="trending-up">
                Forecast
            </x-ui.nav-item>

            <div class="pt-4 pb-1 px-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Clientes</p>
            </div>

            <x-ui.nav-item href="{{ route('clientes.index') }}" :active="request()->routeIs('clientes.*')" icon="users">
                Clientes
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('seguimientos.index') }}" :active="request()->routeIs('seguimientos.*')" icon="clock">
                Seguimientos
            </x-ui.nav-item>

            @role('admin|gerente')
            <div class="pt-4 pb-1 px-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Configuración</p>
            </div>
            <x-ui.nav-item href="{{ route('reportes.index') }}" :active="request()->routeIs('reportes.*')" icon="bar-chart">
                Reportes
            </x-ui.nav-item>
            @endrole

            @role('admin')
            <x-ui.nav-item href="{{ route('maestros.index') }}" :active="request()->routeIs('maestros.*')" icon="settings">
                Maestros CRM
            </x-ui.nav-item>
            @endrole
        </nav>

        {{-- User footer --}}
        <div class="px-3 py-4 border-t border-slate-800">
            <div class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-colors">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-semibold">
                    {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? '' }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ auth()->user()->getRoleNames()->first() ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-white transition-colors">
                        <x-ui.icon name="log-out" class="w-4 h-4"/>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">

        {{-- Topbar --}}
        <header class="sticky top-0 z-40 flex h-16 items-center gap-4 bg-white border-b border-slate-200 px-4 sm:px-6">
            <h1 class="text-sm font-semibold text-slate-900">{{ $title ?? 'SFconnecting' }}</h1>
            <div class="flex-1"></div>
            {{ $actions ?? '' }}
        </header>

        {{-- Content --}}
        <main class="flex-1 p-4 sm:p-6">
            {{ $slot }}
        </main>
    </div>
</div>

{{-- Toast Container --}}
<div
    x-data
    class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 w-80"
    aria-live="polite"
>
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :class="{
                'bg-emerald-50 border-emerald-200 text-emerald-800': toast.type === 'success',
                'bg-red-50 border-red-200 text-red-800': toast.type === 'error',
                'bg-amber-50 border-amber-200 text-amber-800': toast.type === 'warning',
            }"
            class="flex items-start gap-3 rounded-xl border px-4 py-3 shadow-lg text-sm"
        >
            <span class="flex-1" x-text="toast.message"></span>
            <button @click="$store.toast.remove(toast.id)" class="opacity-60 hover:opacity-100">×</button>
        </div>
    </template>
</div>

@stack('scripts')
</body>
</html>
