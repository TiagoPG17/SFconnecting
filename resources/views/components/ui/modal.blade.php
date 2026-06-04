@props(['title' => '', 'size' => 'md'])

@php
$sizes = [
    'sm' => 'max-w-md',
    'md' => 'max-w-lg',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.window="open = false"
>
    {{-- Backdrop --}}
    <div
        class="absolute inset-0 bg-black/40 backdrop-blur-sm"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
    ></div>

    {{-- Panel --}}
    <div
        class="relative w-full {{ $sizeClass }} bg-white rounded-2xl shadow-2xl overflow-hidden"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        @if($title)
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                <x-ui.icon name="x" class="w-5 h-5"/>
            </button>
        </div>
        @endif
        <div class="px-6 py-5">{{ $slot }}</div>
    </div>
</div>
