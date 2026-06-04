<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SFconnecting' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full font-sans antialiased" x-data>

<div class="min-h-full flex">
    {{-- Panel izquierdo decorativo --}}
    <div class="hidden lg:flex lg:flex-1 bg-slate-900 flex-col justify-between p-12">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-500 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-white font-semibold">SFconnecting</span>
        </div>
        <div>
            <p class="text-3xl font-bold text-white leading-tight">
                CRM Comercial<br>Enterprise
            </p>
            <p class="mt-4 text-slate-400 text-sm leading-relaxed">
                Pipeline, prospectos, forecast y conversión.<br>
                Todo en un solo lugar.
            </p>
        </div>
        <div class="flex gap-6 text-slate-500 text-xs">
            <span>Pipeline dinámico</span>
            <span>Forecast en tiempo real</span>
            <span>Integración ERP</span>
        </div>
    </div>

    {{-- Panel derecho --}}
    <div class="flex flex-1 flex-col justify-center py-12 px-4 sm:px-6 lg:max-w-md lg:px-12 xl:max-w-lg">
        {{ $slot }}
    </div>
</div>

</body>
</html>
