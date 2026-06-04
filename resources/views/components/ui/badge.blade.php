@props(['color' => '#6B7280', 'size' => 'sm'])

@php
$sizes = [
    'xs' => 'px-1.5 py-0.5 text-xs',
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-sm',
];
$sizeClass = $sizes[$size] ?? $sizes['sm'];

// Calcola colore testo contrasto
$hex = ltrim($color, '#');
$r = hexdec(substr($hex, 0, 2));
$g = hexdec(substr($hex, 2, 2));
$b = hexdec(substr($hex, 4, 2));
$luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
$textColor = $luminance > 0.5 ? 'rgba(0,0,0,0.8)' : '#ffffff';
@endphp

<span
    style="background-color: {{ $color }}1a; color: {{ $color }}; border-color: {{ $color }}33;"
    class="{{ $sizeClass }} inline-flex items-center rounded-full font-medium border">
    {{ $slot }}
</span>
