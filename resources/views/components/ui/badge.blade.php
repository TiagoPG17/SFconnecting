@props(['color' => '#6B7280', 'size' => 'sm'])

@php
$sizes = [
    'xs' => 'px-1.5 py-0.5 text-xs',
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-sm',
];
$sizeClass = $sizes[$size] ?? $sizes['sm'];

// La mayoría de los llamadores pasan nombres al estilo Tailwind ("green", "amber", "slate"...)
// en vez de hex; solo los colores que vienen de la tabla de maestros (pipeline, prioridad, etc.)
// son hex reales. Se resuelve el nombre a un hex de referencia antes de calcular contraste.
$palette = [
    'slate' => '#475569', 'gray' => '#4b5563', 'zinc' => '#52525b',
    'red' => '#dc2626', 'rose' => '#be123c', 'orange' => '#c2410c',
    'amber' => '#b45309', 'yellow' => '#a16207', 'green' => '#15803d',
    'emerald' => '#047857', 'teal' => '#0f766e', 'cyan' => '#0e7490',
    'blue' => '#1d4ed8', 'indigo' => '#4338ca', 'purple' => '#7e22ce',
    'pink' => '#be185d',
];
$resolvedColor = str_starts_with($color, '#') ? $color : ($palette[$color] ?? $color);

// El fondo del badge es este color con ~10% de opacidad sobre blanco,
// así que el texto necesita contraste 4.5:1 contra blanco, no contra $color.
// Si el color crudo no alcanza, se oscurece en HSL hasta cumplirlo.
$relativeLuminance = function (string $hexColor): float {
    $hexColor = ltrim($hexColor, '#');
    foreach (['r', 'g', 'b'] as $i => $channel) {
        $c = hexdec(substr($hexColor, $i * 2, 2)) / 255;
        $$channel = $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
    }
    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
};
$contrastRatio = function (float $l1, float $l2): float {
    [$lighter, $darker] = $l1 > $l2 ? [$l1, $l2] : [$l2, $l1];
    return ($lighter + 0.05) / ($darker + 0.05);
};
$hexToHsl = function (string $hexColor): array {
    $hexColor = ltrim($hexColor, '#');
    $r = hexdec(substr($hexColor, 0, 2)) / 255;
    $g = hexdec(substr($hexColor, 2, 2)) / 255;
    $b = hexdec(substr($hexColor, 4, 2)) / 255;
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $l = ($max + $min) / 2;
    if ($max === $min) {
        return [0, 0, $l];
    }
    $d = $max - $min;
    $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
    $h = match ($max) {
        $r => (($g - $b) / $d + ($g < $b ? 6 : 0)),
        $g => ($b - $r) / $d + 2,
        default => ($r - $g) / $d + 4,
    } / 6;
    return [$h, $s, $l];
};
$hslToHex = function (float $h, float $s, float $l): string {
    if ($s === 0.0) {
        $r = $g = $b = $l;
    } else {
        $hue2rgb = function ($p, $q, $t) {
            if ($t < 0) $t += 1;
            if ($t > 1) $t -= 1;
            if ($t < 1 / 6) return $p + ($q - $p) * 6 * $t;
            if ($t < 1 / 2) return $q;
            if ($t < 2 / 3) return $p + ($q - $p) * (2 / 3 - $t) * 6;
            return $p;
        };
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $r = $hue2rgb($p, $q, $h + 1 / 3);
        $g = $hue2rgb($p, $q, $h);
        $b = $hue2rgb($p, $q, $h - 1 / 3);
    }
    return sprintf('#%02x%02x%02x', round($r * 255), round($g * 255), round($b * 255));
};

$whiteLuminance = 1.0;
$textColor = $resolvedColor;
[$h, $s, $l] = $hexToHsl($resolvedColor);
$steps = 0;
while ($contrastRatio($relativeLuminance($textColor), $whiteLuminance) < 4.5 && $steps < 20) {
    $l = max(0, $l - 0.04);
    $textColor = $hslToHex($h, $s, $l);
    $steps++;
}
@endphp

<span
    style="background-color: {{ $resolvedColor }}1a; color: {{ $textColor }}; border-color: {{ $resolvedColor }}4d;"
    class="{{ $sizeClass }} inline-flex items-center rounded-full font-medium border">
    {{ $slot }}
</span>
