@props(['href', 'active' => false, 'icon' => 'circle'])

<a href="{{ $href }}"
   class="{{ $active
       ? 'bg-slate-800 text-white'
       : 'text-slate-400 hover:bg-slate-800 hover:text-white'
   }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors">
    <x-ui.icon :name="$icon" class="w-4 h-4 shrink-0"/>
    {{ $slot }}
</a>
