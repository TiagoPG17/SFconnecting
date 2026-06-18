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
    <style>
        @keyframes sfblink { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.2;transform:scale(0.85)} }
        .notif-dot { animation: sfblink 1.1s ease-in-out infinite; }
    </style>
    {{-- Anti-flash: aplica preferencias guardadas antes de que cargue Alpine --}}
    <script>
        try {
            var p = JSON.parse(localStorage.getItem('sfcrm_a11y') || '{}');
            var sz = { sm:'13px', lg:'18px', xl:'20px' };
            if (p.zoom && p.zoom !== 'normal') document.documentElement.style.fontSize = sz[p.zoom];
            if (p.contrast)  document.documentElement.classList.add('a11y-contrast');
            if (p.noMotion)  document.documentElement.classList.add('a11y-no-motion');
            if (p.focusMode) document.documentElement.classList.add('a11y-focus');
        } catch(e) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false }">

{{-- Overlay móvil --}}
<div
    x-show="sidebarOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="sidebarOpen = false"
    class="fixed inset-0 z-30 bg-black/50 lg:hidden"
    x-cloak
></div>

<div class="flex h-full">

    {{-- Sidebar --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 flex flex-col w-64 bg-slate-900 transition-transform duration-200 ease-in-out lg:translate-x-0"
    >
        {{-- Logo --}}
        <div class="flex h-16 items-center gap-3 px-6 border-b border-slate-800 shrink-0">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #1d4ed8 0%, #4338ca 100%); box-shadow: 0 4px 14px rgba(29,78,216,0.45)">
                <svg viewBox="0 0 24 24" fill="none" class="w-6 h-6">
                    {{-- Asa del maletín --}}
                    <path d="M8 8V6.5A2.5 2.5 0 0110.5 4h3A2.5 2.5 0 0116 6.5V8" stroke="white" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    {{-- Cuerpo del maletín --}}
                    <rect x="2" y="8" width="20" height="13" rx="2.5" fill="rgba(255,255,255,0.18)" stroke="white" stroke-width="1.7"/>
                    {{-- $ centrado dentro del cuerpo --}}
                    <text x="12" y="18.5" text-anchor="middle" dominant-baseline="auto" fill="white" font-size="8.5" font-weight="800" font-family="Arial, sans-serif">$</text>
                </svg>
            </div>
            <span class="text-white font-semibold text-sm tracking-wide">SFconnecting</span>
            {{-- Cerrar en móvil --}}
            <button @click="sidebarOpen = false" class="ml-auto text-slate-400 hover:text-white lg:hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">


            @role('comercial|admin')
            <x-ui.nav-item href="{{ route('dash.comercial') }}" :active="request()->routeIs('dash.comercial')" icon="home">
                Dashboard
            </x-ui.nav-item>
            @endrole
            @role('comercial|admin')
            <x-ui.nav-item href="{{ route('dash.vendedor') }}" :active="request()->routeIs('dash.vendedor')" icon="trending-up">
                Mi desempeño
            </x-ui.nav-item>
            @endrole
            @role('admin|gerente')
            <x-ui.nav-item href="{{ route('dash.gerencial') }}" :active="request()->routeIs('dash.gerencial')" icon="bar-chart">
                Visión gerencial
            </x-ui.nav-item>
            @endrole

            {{-- Comercial --}}
            <div class="pt-4 pb-1 px-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Comercial</p>
            </div>
            <x-ui.nav-item href="{{ route('prospectos.index') }}" :active="request()->routeIs('prospectos.*')" icon="user-plus">
                Prospectos
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('negocios.index') }}" :active="request()->routeIs('negocios.*') && !request()->routeIs('negocios.kanban')" icon="briefcase">
                Negocios
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('negocios.kanban') }}" :active="request()->routeIs('negocios.kanban')" icon="layout">
                Pipeline Kanban
            </x-ui.nav-item>

            {{-- Clientes --}}
            <div class="pt-4 pb-1 px-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Clientes</p>
            </div>
            <x-ui.nav-item href="{{ route('clientes.index') }}" :active="request()->routeIs('clientes.*') && !request()->routeIs('clientes-huerfanos.*')" icon="users">
                Clientes
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('clientes-huerfanos.index') }}" :active="request()->routeIs('clientes-huerfanos.*')" icon="user-x">
                Clientes huérfanos
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('seguimientos.index') }}" :active="request()->routeIs('seguimientos.*')" icon="clock">
                Seguimientos
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('calendario.index') }}" :active="request()->routeIs('calendario.*')" icon="calendar">
                Calendario
            </x-ui.nav-item>

            {{-- Análisis (admin + gerente) --}}
            @role('admin|gerente')
            <div class="pt-4 pb-1 px-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Análisis</p>
            </div>
            <x-ui.nav-item href="{{ route('presupuestos.index') }}" :active="request()->routeIs('presupuestos.*')" icon="trending-up">
                Presupuestos
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('reportes.index') }}" :active="request()->routeIs('reportes.*')" icon="bar-chart">
                Reportes
            </x-ui.nav-item>
            @endrole

            {{-- Administración (admin) --}}
            @role('admin')
            <div class="pt-4 pb-1 px-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Administración</p>
            </div>
            <x-ui.nav-item href="{{ route('mapeo-vendedores.index') }}" :active="request()->routeIs('mapeo-vendedores.*')" icon="users">
                Mapeo vendedores
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('maestros.index') }}" :active="request()->routeIs('maestros.*')" icon="settings">
                Maestros CRM
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('usuarios.index') }}" :active="request()->routeIs('usuarios.*')" icon="users">
                Usuarios
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('auditoria.index') }}" :active="request()->routeIs('auditoria.*')" icon="shield">
                Auditoría
            </x-ui.nav-item>
            <x-ui.nav-item href="{{ route('admin.gestion.index') }}" :active="request()->routeIs('admin.gestion.*')" icon="trash">
                Gestión registros
            </x-ui.nav-item>
            @endrole

        </nav>

        {{-- User footer --}}
        <div class="px-3 py-4 border-t border-slate-800 shrink-0">
            <div class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-colors">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-semibold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
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

        {{-- Topbar global --}}
        <header class="sticky top-0 z-20 flex h-12 items-center bg-white border-b border-slate-200 px-4 shrink-0">
            {{-- Hamburger (solo móvil) --}}
            <button
                @click="sidebarOpen = true"
                class="lg:hidden text-slate-500 hover:text-slate-700 transition-colors p-1 -ml-1 rounded-lg hover:bg-slate-100 mr-2"
                aria-label="Abrir menú"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            {{-- Título (solo móvil) --}}
            <span class="lg:hidden text-sm font-semibold text-slate-900 truncate flex-1">{{ $title ?? 'SFconnecting' }}</span>
            {{-- Reloj Colombia (desktop) --}}
            <div class="hidden lg:flex items-center gap-2 flex-1"
                 x-data="{
                    hora: '',
                    fecha: '',
                    init() {
                        this.tick();
                        setInterval(() => this.tick(), 1000);
                    },
                    tick() {
                        const n = new Date();
                        this.hora  = n.toLocaleTimeString('es-CO', { timeZone: 'America/Bogota', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                        this.fecha = n.toLocaleDateString('es-CO', { timeZone: 'America/Bogota', weekday: 'short', day: 'numeric', month: 'short' });
                    }
                 }">
                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                </svg>
                <span class="text-sm font-medium text-slate-700 tnum" x-text="hora"></span>
                <span class="text-xs text-slate-400" x-text="fecha"></span>
            </div>
            {{-- Botones derechos --}}
            <div class="flex items-center gap-1">
                {{-- Notificaciones --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                    <div class="relative">
                        <button @click="open = !open" title="Notificaciones"
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-500 hover:bg-amber-100 hover:text-amber-600 transition-colors">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </button>
                        @if($notificaciones->isNotEmpty())
                        <span class="notif-dot pointer-events-none"
                              style="position:absolute;top:4px;left:4px;width:9px;height:9px;background:#ef4444;border-radius:50%;border:2px solid white;display:inline-block"></span>
                        @endif
                    </div>
                    {{-- Panel dropdown --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         x-cloak
                         class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-lg border border-slate-200 z-50 overflow-hidden origin-top-right">
                        {{-- Cabecera --}}
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                            <span class="text-sm font-semibold text-slate-900">Notificaciones</span>
                            @if($notificaciones->isNotEmpty())
                            <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">
                                {{ $notificaciones->count() }} pendiente{{ $notificaciones->count() > 1 ? 's' : '' }}
                            </span>
                            @endif
                        </div>
                        {{-- Lista --}}
                        @if($notificaciones->isNotEmpty())
                        <ul class="divide-y divide-slate-50 max-h-72 overflow-y-auto">
                            @foreach($notificaciones as $noti)
                            <li class="hover:bg-slate-50 transition-colors">
                                <a href="{{ route('seguimientos.index') }}" class="flex items-start gap-3 px-4 py-3">
                                    <div class="mt-0.5 w-7 h-7 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900 truncate">
                                            {{ $noti->cliente?->razon_social ?? $noti->prospecto?->empresa ?? 'Sin entidad' }}
                                        </p>
                                        <p class="text-xs text-slate-500 truncate">{{ Str::limit($noti->descripcion, 55) }}</p>
                                        <p class="text-xs text-red-600 font-medium mt-0.5 capitalize">
                                            {{ $noti->tipo }} · {{ ($noti->proxima_fecha ?? $noti->fecha_seguimiento)->diffForHumans() }}
                                        </p>
                                    </div>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <div class="px-4 py-8 text-center text-sm text-slate-400">
                            Sin seguimientos pendientes
                        </div>
                        @endif
                        {{-- Pie --}}
                        <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50">
                            <a href="{{ route('seguimientos.index') }}"
                               class="text-xs font-medium text-teal-700 hover:text-teal-900 transition-colors">
                                Ver todos los seguimientos →
                            </a>
                        </div>
                    </div>
                </div>
                {{-- Accesibilidad --}}
                <button
                    @click="$store.ui.a11yOpen = true"
                    title="Opciones de accesibilidad (Alt+A)"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors"
                >
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a1 1 0 100 2 1 1 0 000-2zM12 5v4m0 0l-3 3m3-3l3 3M9 12v5a3 3 0 006 0v-5"/>
                    </svg>
                </button>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-4 sm:p-6">
            {{-- Page header --}}
            @if(!($hidePageTitle ?? false) || isset($actions))
            <div class="flex items-center justify-between mb-6">
                @unless($hidePageTitle ?? false)
                <h1 class="text-xl font-bold text-slate-900 hidden lg:block">{{ $title ?? 'SFconnecting' }}</h1>
                @endunless
                @if(isset($actions))
                <div class="flex items-center gap-2 ml-auto">{{ $actions }}</div>
                @endif
            </div>
            @endif
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
            <button @click="$store.toast.remove(toast.id)" class="opacity-60 hover:opacity-100 text-lg leading-none">×</button>
        </div>
    </template>
</div>

@stack('scripts')

{{-- ══════════════════════════════════════════
     PANEL DE ACCESIBILIDAD
══════════════════════════════════════════ --}}
<div
    x-data
    x-init="
        document.addEventListener('keydown', e => {
            if (e.altKey && e.key.toLowerCase() === 'a') {
                $store.ui.a11yOpen = !$store.ui.a11yOpen;
                e.preventDefault();
            }
            if (e.key === 'Escape') $store.ui.a11yOpen = false;
        });
    "
>
    {{-- Overlay --}}
    <div
        x-show="$store.ui.a11yOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="$store.ui.a11yOpen = false"
        class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm"
        x-cloak
    ></div>

    {{-- Panel --}}
    <div
        x-show="$store.ui.a11yOpen"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-x-8"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-8"
        class="fixed right-0 top-0 bottom-0 z-50 w-80 bg-white shadow-2xl flex flex-col"
        x-cloak
    >
        {{-- Header del panel --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a1 1 0 100 2 1 1 0 000-2zM12 5v4m0 0l-3 3m3-3l3 3M9 12v5a3 3 0 006 0v-5"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900">Accesibilidad</p>
                    <p class="text-xs text-slate-400">Personaliza tu experiencia</p>
                </div>
            </div>
            <button @click="$store.ui.a11yOpen = false" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Contenido scrollable --}}
        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-6">

            {{-- Tamaño de pantalla --}}
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tamaño de pantalla</p>
                <p class="text-xs text-slate-400 mb-3">Ajusta el zoom de toda la interfaz</p>
                <div class="grid grid-cols-4 gap-1.5">
                    @foreach([
                        ['val' => 'sm',     'label' => 'Compacto', 'sub' => '13px'],
                        ['val' => 'normal', 'label' => 'Normal',   'sub' => '16px'],
                        ['val' => 'lg',     'label' => 'Grande',   'sub' => '18px'],
                        ['val' => 'xl',     'label' => 'Máximo',   'sub' => '20px'],
                    ] as $opt)
                    <button
                        @click="$store.a11y.setZoom('{{ $opt['val'] }}')"
                        :class="$store.a11y.zoom === '{{ $opt['val'] }}' ? 'bg-blue-600 text-white shadow-sm shadow-blue-200 ring-2 ring-blue-400 ring-offset-1' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="flex flex-col items-center gap-0.5 py-2.5 px-1 rounded-xl text-xs font-semibold transition-all"
                    >
                        <span class="text-base font-black leading-none">{{ $opt['sub'] }}</span>
                        <span class="text-xs opacity-80">{{ $opt['label'] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="h-px bg-slate-100"></div>

            {{-- Alto contraste --}}
            <div>
                <div class="flex items-start justify-between">
                    <div class="flex-1 pr-4">
                        <p class="text-sm font-semibold text-slate-800">Alto contraste</p>
                        <p class="text-xs text-slate-400 mt-0.5">Texto secundario más oscuro y bordes más visibles</p>
                    </div>
                    <button
                        @click="$store.a11y.toggleContrast()"
                        :class="$store.a11y.contrast ? 'bg-blue-600' : 'bg-slate-200'"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-600"
                        role="switch"
                        :aria-checked="$store.a11y.contrast.toString()"
                    >
                        <span
                            :class="$store.a11y.contrast ? 'translate-x-5' : 'translate-x-0.5'"
                            class="pointer-events-none inline-block h-5 w-5 mt-0.5 transform rounded-full bg-white shadow-md transition-transform duration-200"
                        ></span>
                    </button>
                </div>
                <div x-show="$store.a11y.contrast" class="mt-2 flex items-center gap-1.5 text-xs text-blue-600 bg-blue-50 rounded-lg px-2.5 py-1.5">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    Contraste elevado activo
                </div>
            </div>

            <div class="h-px bg-slate-100"></div>

            {{-- Reducir movimiento --}}
            <div>
                <div class="flex items-start justify-between">
                    <div class="flex-1 pr-4">
                        <p class="text-sm font-semibold text-slate-800">Reducir movimiento</p>
                        <p class="text-xs text-slate-400 mt-0.5">Desactiva animaciones y transiciones</p>
                    </div>
                    <button
                        @click="$store.a11y.toggleMotion()"
                        :class="$store.a11y.noMotion ? 'bg-blue-600' : 'bg-slate-200'"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors duration-200"
                        role="switch"
                        :aria-checked="$store.a11y.noMotion.toString()"
                    >
                        <span
                            :class="$store.a11y.noMotion ? 'translate-x-5' : 'translate-x-0.5'"
                            class="pointer-events-none inline-block h-5 w-5 mt-0.5 transform rounded-full bg-white shadow-md transition-transform duration-200"
                        ></span>
                    </button>
                </div>
            </div>

            <div class="h-px bg-slate-100"></div>

            {{-- Foco de teclado --}}
            <div>
                <div class="flex items-start justify-between">
                    <div class="flex-1 pr-4">
                        <p class="text-sm font-semibold text-slate-800">Navegación por teclado</p>
                        <p class="text-xs text-slate-400 mt-0.5">Resalta el elemento enfocado al navegar con Tab</p>
                    </div>
                    <button
                        @click="$store.a11y.toggleFocus()"
                        :class="$store.a11y.focusMode ? 'bg-blue-600' : 'bg-slate-200'"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors duration-200"
                        role="switch"
                        :aria-checked="$store.a11y.focusMode.toString()"
                    >
                        <span
                            :class="$store.a11y.focusMode ? 'translate-x-5' : 'translate-x-0.5'"
                            class="pointer-events-none inline-block h-5 w-5 mt-0.5 transform rounded-full bg-white shadow-md transition-transform duration-200"
                        ></span>
                    </button>
                </div>
            </div>

            <div class="h-px bg-slate-100"></div>

            {{-- Atajo de teclado --}}
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-3.5">
                <p class="text-xs font-semibold text-slate-600 mb-2">Atajos de teclado</p>
                <div class="flex items-center justify-between">
                    <p class="text-xs text-slate-500">Abrir / cerrar este panel</p>
                    <div class="flex items-center gap-1">
                        <kbd class="text-xs bg-white border border-slate-200 text-slate-600 px-1.5 py-0.5 rounded font-mono shadow-sm">Alt</kbd>
                        <span class="text-slate-300 text-xs">+</span>
                        <kbd class="text-xs bg-white border border-slate-200 text-slate-600 px-1.5 py-0.5 rounded font-mono shadow-sm">A</kbd>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 border-t border-slate-100 shrink-0">
            <button
                @click="$store.a11y.reset()"
                class="w-full flex items-center justify-center gap-2 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors border border-slate-200"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Restaurar valores por defecto
            </button>
        </div>
    </div>
</div>

</body>
</html>
