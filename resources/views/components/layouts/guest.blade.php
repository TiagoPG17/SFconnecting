<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SFconnecting' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full font-sans antialiased" x-data>

<div class="min-h-full flex">

    {{-- Panel izquierdo --}}
    <div class="hidden lg:flex lg:w-[55%] relative overflow-hidden flex-col justify-between p-14"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1e40af 100%);">

        {{-- Círculos decorativos de fondo --}}
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full opacity-10"
             style="background: radial-gradient(circle, #60a5fa, transparent)"></div>
        <div class="absolute -bottom-40 -right-20 w-[500px] h-[500px] rounded-full opacity-10"
             style="background: radial-gradient(circle, #3b82f6, transparent)"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 rounded-full opacity-5"
             style="background: radial-gradient(circle, #93c5fd, transparent)"></div>

        {{-- Logo --}}
        <div class="relative flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                 style="background: rgba(59,130,246,0.3); border: 1px solid rgba(96,165,250,0.3);">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-white font-semibold tracking-wide text-lg">SFconnecting</span>
        </div>

        {{-- Contenido central --}}
        <div class="relative">
            <p class="text-xs font-semibold uppercase tracking-widest text-blue-400 mb-4">CRM Comercial Enterprise</p>
            <h1 class="text-4xl font-bold text-white leading-snug">
                Gestiona tu pipeline<br>
                <span class="text-blue-400">con precisión.</span>
            </h1>
            <p class="mt-5 text-slate-400 text-sm leading-relaxed max-w-sm">
                Prospectos, seguimientos, forecast y conversión centralizados.
                Integración nativa con ERP Contiflex.
            </p>

            {{-- Features --}}
            <div class="mt-10 space-y-4">
                @foreach([
                    ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label' => 'Pipeline dinámico en tiempo real'],
                    ['icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'label' => 'Forecast y métricas de conversión'],
                    ['icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4', 'label' => 'Integración directa con Contiflex ERP'],
                ] as $feature)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background: rgba(59,130,246,0.15); border: 1px solid rgba(96,165,250,0.2);">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-slate-300 text-sm">{{ $feature['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Footer --}}
        <div class="relative flex items-center gap-2">
            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
            <span class="text-slate-500 text-xs">Sistema operando con normalidad</span>
        </div>
    </div>

    {{-- Panel derecho --}}
    <div class="flex flex-1 flex-col justify-center items-center bg-slate-50 py-12 px-6 sm:px-10">
        {{ $slot }}
    </div>

</div>

</body>
</html>
