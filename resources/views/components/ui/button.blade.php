@props([
    'variant' => 'primary',
    'size'    => 'md',
    'type'    => 'button',
    'href'    => null,
])

@php
$variants = [
    'primary'   => 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm',
    'secondary' => 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 shadow-sm',
    'danger'    => 'bg-red-600 text-white hover:bg-red-700 shadow-sm',
    'ghost'     => 'text-slate-600 hover:bg-slate-100',
    'link'      => 'text-blue-600 hover:underline p-0',
];
$sizes = [
    'xs' => 'px-2.5 py-1.5 text-xs',
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-5 py-2.5 text-base',
];
$base = 'inline-flex items-center gap-2 font-medium rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed';
$cls  = "{$base} {$variants[$variant]} {$sizes[$size]}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $cls]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $cls]) }}>{{ $slot }}</button>
@endif
