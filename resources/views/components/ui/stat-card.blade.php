@props(['label', 'value', 'icon' => null, 'color' => 'blue', 'trend' => null, 'trendLabel' => null])

@php
$colors = [
    'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'bg-blue-100 text-blue-600',   'text' => 'text-blue-600'],
    'green'  => ['bg' => 'bg-emerald-50', 'icon' => 'bg-emerald-100 text-emerald-600', 'text' => 'text-emerald-600'],
    'orange' => ['bg' => 'bg-orange-50',  'icon' => 'bg-orange-100 text-orange-600',  'text' => 'text-orange-600'],
    'purple' => ['bg' => 'bg-purple-50',  'icon' => 'bg-purple-100 text-purple-600',  'text' => 'text-purple-600'],
    'red'    => ['bg' => 'bg-red-50',     'icon' => 'bg-red-100 text-red-600',     'text' => 'text-red-600'],
];
$c = $colors[$color] ?? $colors['blue'];
@endphp

<x-ui.card class="p-5">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $value }}</p>
            @if($trend !== null)
                <p class="mt-1 text-xs {{ $trend >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $trend >= 0 ? '↑' : '↓' }} {{ abs($trend) }}% {{ $trendLabel }}
                </p>
            @endif
        </div>
        @if($icon)
            <div class="w-10 h-10 rounded-xl {{ $c['icon'] }} flex items-center justify-center shrink-0">
                <x-ui.icon :name="$icon" class="w-5 h-5"/>
            </div>
        @endif
    </div>
</x-ui.card>
