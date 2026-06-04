<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-ui.stat-card label="Total prospectos" :value="$datos['prospectos_total'] ?? 0" color="blue" icon="user-plus"/>
    <x-ui.stat-card label="Convertidos" :value="$datos['prospectos_convertidos'] ?? 0" color="green" icon="check"/>
    <x-ui.stat-card label="Negocios ganados" :value="$datos['negocios_ganados'] ?? 0" color="purple" icon="trending-up"/>
</div>

<x-ui.card>
    <div class="p-6">
        <h3 class="text-sm font-semibold text-slate-900 mb-4">Tasa de conversión prospecto → cliente</h3>
        @php $tasa = $datos['tasa_conversion'] ?? 0; @endphp
        <div class="flex items-center gap-4 mb-2">
            <x-ui.progress :value="round($tasa)" class="flex-1"/>
            <span class="text-2xl font-bold text-slate-900 w-16 text-right">{{ number_format($tasa, 1) }}%</span>
        </div>
        <p class="text-xs text-slate-500">
            {{ $datos['prospectos_convertidos'] ?? 0 }} convertidos de {{ $datos['prospectos_total'] ?? 0 }} prospectos totales
        </p>
    </div>
</x-ui.card>
