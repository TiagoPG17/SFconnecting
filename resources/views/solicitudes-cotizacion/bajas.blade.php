<x-layouts.app title="Bajas — Solicitudes de Cotización">

    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Auditoría de bajas</h1>
            <p class="text-xs text-slate-500">Solicitudes de cotización eliminadas en SGP (soft delete — nunca se borran físicamente).</p>
        </div>
        <a href="{{ route('solicitudes-cotizacion.index') }}"
           class="inline-flex items-center gap-1.5 text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-medium transition-colors">
            <x-ui.icon name="arrow-left" class="w-3.5 h-3.5"/>
            Volver a solicitudes
        </a>
    </div>

    @if(!$erpDisponible)
    <x-ui.card class="p-10 text-center">
        <p class="text-slate-500 text-sm">Sin conexión al ERP. No se pueden cargar las bajas en este momento.</p>
    </x-ui.card>
    @elseif($bajas->isEmpty())
    <x-ui.card class="overflow-hidden">
        <x-ui.empty-state icon="check-circle" title="Sin bajas" description="No hay solicitudes de cotización dadas de baja."/>
    </x-ui.card>
    @else
    <x-ui.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead class="bg-gradient-to-b from-slate-50 to-slate-100/70 border-b-2 border-slate-200">
                    <tr class="text-left text-sm font-bold text-slate-700">
                        <th class="px-4 py-3">Nro. Solicitud</th>
                        <th class="px-4 py-3">Fecha solicitud</th>
                        <th class="px-4 py-3">Comercial</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Fecha de baja</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($bajas as $s)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700">{{ $s->nro_solicitud }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $s->fecha_solicitud?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 whitespace-normal min-w-[140px]">{{ $s->nombre_vendedor ?: $s->vendedor }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900 whitespace-normal min-w-[160px]">{{ $s->cliente ?: $s->cliente_digitado ?: '—' }}</td>
                        <td class="px-4 py-3 text-xs text-red-600 font-medium">{{ $s->fecha_baja?->format('d/m/Y H:i') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">
        {{ $bajas->links() }}
    </div>
    @endif

</x-layouts.app>
