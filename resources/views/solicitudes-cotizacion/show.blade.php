<x-layouts.app title="Solicitud {{ $solicitud->nro_solicitud }}">

    @php
        $colorSituacion = match($solicitud->situacion_cliente) {
            'CLIENTE ASIGNADO' => 'green',
            'VARIOS'           => 'amber',
            default            => 'slate',
        };
    @endphp

    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Solicitud {{ $solicitud->nro_solicitud }}</h1>
            <p class="text-xs text-slate-500">{{ $solicitud->descripcion ?: 'Sin descripción' }}</p>
        </div>
        <a href="{{ route('solicitudes-cotizacion.index') }}"
           class="inline-flex items-center gap-1.5 text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-medium transition-colors">
            <x-ui.icon name="arrow-left" class="w-3.5 h-3.5"/>
            Volver a solicitudes
        </a>
    </div>

    @if($solicitud->estado_registro === 'ELIMINADO')
    <x-ui.card class="p-3 mb-4 border-red-200 bg-red-50">
        <p class="text-xs text-red-700 font-medium">
            Esta solicitud fue dada de baja en SGP el {{ $solicitud->fecha_baja?->format('d/m/Y H:i') }}.
        </p>
    </x-ui.card>
    @endif

    <x-ui.card class="p-5 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-slate-500">Fecha</p>
                <p class="font-medium text-slate-900">{{ $solicitud->fecha_solicitud?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Comercial</p>
                <p class="font-medium text-slate-900">{{ $solicitud->nombre_vendedor ?: $solicitud->vendedor }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Cliente</p>
                <p class="font-medium text-slate-900">{{ $solicitud->cliente ?: $solicitud->cliente_digitado ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">NIT</p>
                <p class="font-medium text-slate-900">{{ $solicitud->nit ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Situación cliente</p>
                <p><x-ui.badge :color="$colorSituacion">{{ $solicitud->situacion_cliente }}</x-ui.badge></p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Estado</p>
                <p class="font-medium text-slate-900">{{ $solicitud->estado }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Tipo de cotización</p>
                <p class="font-medium text-slate-900">{{ $solicitud->tipo_cotizacion ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Moneda / opciones</p>
                <p class="font-medium text-slate-900">{{ $solicitud->tipomoneda ?: '—' }} · {{ $solicitud->nro_opciones ?? '—' }} opc.</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card class="overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Escalas</h2>
            <span class="text-xs text-slate-400">{{ $solicitud->escalas_con_precio }} con precio de {{ $solicitud->escalas }}</span>
        </div>

        @if($solicitud->escalasDetalle->isEmpty())
        <x-ui.empty-state icon="search" title="Sin escalas registradas" description="Esta solicitud aún no tiene escalas sincronizadas."/>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead class="bg-gradient-to-b from-slate-50 to-slate-100/70 border-b-2 border-slate-200">
                    <tr class="text-left text-sm font-bold text-slate-700">
                        <th class="px-4 py-3 text-right">Escala</th>
                        <th class="px-4 py-3">Moneda</th>
                        <th class="px-4 py-3 text-right">Precio unit.</th>
                        <th class="px-4 py-3 text-right">Precio unit. COP</th>
                        <th class="px-4 py-3 text-right">Costo unit. COP</th>
                        <th class="px-4 py-3 text-right">% s/costo</th>
                        <th class="px-4 py-3 text-right">Valor total</th>
                        <th class="px-4 py-3">Cotizador</th>
                        <th class="px-4 py-3">Fecha precio</th>
                        <th class="px-4 py-3">Situación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($solicitud->escalasDetalle as $e)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-right font-semibold text-slate-800">{{ $e->escala }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $e->moneda }}</td>
                        <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $e->precio_unitario, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-slate-700">${{ number_format((float) $e->precio_unitario_cop, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">${{ number_format((float) $e->costo_unitario_cop, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ number_format((float) $e->precio_sobre_costo, 1, ',', '.') }}%</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-800">${{ number_format((float) $e->valor_total_escala, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $e->cotizador ?: '—' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $e->fecha_precio?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge :color="$e->situacion_escala === 'CON PRECIO' ? 'green' : 'amber'">{{ $e->situacion_escala }}</x-ui.badge>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
