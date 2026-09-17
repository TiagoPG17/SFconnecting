<x-layouts.app title="Escalas de Cotización">

    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Escalas de Cotización</h1>
            <p class="text-xs text-slate-500">Detalle de precios por escala, con el comercial que digitó la solicitud.</p>
        </div>
        <a href="{{ route('solicitudes-cotizacion.index') }}"
           class="inline-flex items-center gap-1.5 text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-medium transition-colors">
            <x-ui.icon name="arrow-left" class="w-3.5 h-3.5"/>
            Volver a solicitudes
        </a>
    </div>

    {{-- Filtros --}}
    <form method="GET" class="flex items-center gap-3 flex-wrap mb-5">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input type="text" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="NIT o nombre del cliente..."
                   class="w-64 pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        </div>

        <select name="vendedor" onchange="this.form.submit()"
                class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Todos los comerciales</option>
            @foreach($vendedores as $v)
                <option value="{{ $v->vendedor }}" @selected(($filtros['vendedor'] ?? '') === $v->vendedor)>{{ $v->nombre_vendedor ?: $v->vendedor }}</option>
            @endforeach
        </select>

        <select name="situacion_escala" onchange="this.form.submit()"
                class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Precio: todas</option>
            <option value="CON PRECIO" @selected(($filtros['situacion_escala'] ?? '') === 'CON PRECIO')>Con precio</option>
            <option value="SIN PRECIO" @selected(($filtros['situacion_escala'] ?? '') === 'SIN PRECIO')>Sin precio</option>
        </select>

        <label class="inline-flex items-center gap-2 text-sm text-slate-600 bg-white border border-slate-200 rounded-xl px-3 py-2 cursor-pointer">
            <input type="checkbox" name="mes_actual" value="1" @checked($filtros['mes_actual'] ?? false) onchange="this.form.submit()"
                   class="rounded border-slate-300 text-blue-600 focus:ring-blue-400">
            Solo mes en curso
        </label>

        <button type="submit" class="text-sm px-3 py-2 rounded-xl bg-slate-800 text-white font-medium hover:bg-slate-700 transition-colors">
            Buscar
        </button>
    </form>

    @if(!$erpDisponible)
    <x-ui.card class="p-10 text-center">
        <p class="text-slate-500 text-sm">Sin conexión al ERP. No se pueden cargar las escalas en este momento.</p>
    </x-ui.card>
    @elseif($escalas->isEmpty())
    <x-ui.card class="overflow-hidden">
        <x-ui.empty-state icon="search" title="Sin escalas" description="No hay escalas que coincidan con el filtro seleccionado."/>
    </x-ui.card>
    @else
    <x-ui.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead class="bg-gradient-to-b from-slate-50 to-slate-100/70 border-b-2 border-slate-200">
                    <tr class="text-left text-sm font-bold text-slate-700">
                        <th class="px-4 py-3">Nro. Solicitud</th>
                        <th class="px-4 py-3">Comercial</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3 text-right">Escala</th>
                        <th class="px-4 py-3">Moneda</th>
                        <th class="px-4 py-3 text-right">Precio unit.</th>
                        <th class="px-4 py-3 text-right">Precio unit. COP</th>
                        <th class="px-4 py-3 text-right">Costo unit. COP</th>
                        <th class="px-4 py-3 text-right">% s/costo</th>
                        <th class="px-4 py-3 text-right">Valor total</th>
                        <th class="px-4 py-3">Situación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($escalas as $e)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('solicitudes-cotizacion.show', $e->nro_solicitud) }}" class="font-mono text-xs font-semibold text-blue-700 hover:underline">
                                {{ $e->nro_solicitud }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-600 whitespace-normal min-w-[140px]">{{ $e->vendedor }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900 whitespace-normal min-w-[160px]">{{ $e->cliente }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-800">{{ $e->escala }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $e->moneda }}</td>
                        <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $e->precio_unitario, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-slate-700">${{ number_format((float) $e->precio_unitario_cop, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">${{ number_format((float) $e->costo_unitario_cop, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ number_format((float) $e->precio_sobre_costo, 1, ',', '.') }}%</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-800">${{ number_format((float) $e->valor_total_escala, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge :color="$e->situacion_escala === 'CON PRECIO' ? 'green' : 'amber'">{{ $e->situacion_escala }}</x-ui.badge>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">
        {{ $escalas->links() }}
    </div>
    @endif

</x-layouts.app>
