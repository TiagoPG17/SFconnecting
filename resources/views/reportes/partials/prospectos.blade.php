<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <x-ui.stat-card label="Total prospectos" :value="$datos['total'] ?? 0" color="blue" icon="user-plus"/>
    <x-ui.stat-card label="Convertidos" :value="$datos['convertidos'] ?? 0" color="green" icon="check"/>
</div>

@if(!empty($datos['por_estado']))
<x-ui.card class="mb-6">
    <div class="p-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-900">Por estado de pipeline</h3>
    </div>
    <div class="divide-y divide-slate-100">
        @foreach($datos['por_estado'] as $nombre => $total)
        <div class="flex items-center justify-between px-4 py-2.5">
            <span class="text-sm text-slate-800">{{ $nombre }}</span>
            <span class="text-sm font-semibold text-slate-900">{{ $total }}</span>
        </div>
        @endforeach
    </div>
</x-ui.card>
@endif

@if(!empty($datos['por_origen']))
<x-ui.card>
    <div class="p-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-900">Por origen</h3>
    </div>
    <div class="divide-y divide-slate-100">
        @foreach($datos['por_origen'] as $nombre => $total)
        <div class="flex items-center justify-between px-4 py-2.5">
            <span class="text-sm text-slate-800">{{ $nombre }}</span>
            <span class="text-sm font-semibold text-slate-900">{{ $total }}</span>
        </div>
        @endforeach
    </div>
</x-ui.card>
@endif
