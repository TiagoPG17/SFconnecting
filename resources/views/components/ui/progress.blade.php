@props(['value' => 0, 'color' => '#3b82f6', 'size' => 'sm', 'showLabel' => false])

@php
$heights = ['xs' => 'h-1', 'sm' => 'h-1.5', 'md' => 'h-2', 'lg' => 'h-3'];
$h = $heights[$size] ?? $heights['sm'];
$pct = min(100, max(0, (int) $value));
@endphp

<div class="w-full">
    @if($showLabel)
        <div class="flex justify-between text-xs text-slate-500 mb-1">
            <span>{{ $slot }}</span>
            <span>{{ $pct }}%</span>
        </div>
    @endif
    <div class="w-full bg-slate-100 rounded-full {{ $h }} overflow-hidden">
        <div
            class="{{ $h }} rounded-full transition-all duration-500"
            style="width: {{ $pct }}%; background-color: {{ $color }};"
        ></div>
    </div>
</div>
