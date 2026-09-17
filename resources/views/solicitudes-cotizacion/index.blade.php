<x-layouts.app title="Solicitudes de Cotización">

    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Solicitudes de Cotización</h1>
            <p class="text-xs text-slate-500">Datos de SGP sincronizados cada hora desde el 2026-01-01.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('solicitudes-cotizacion.escalas') }}"
               class="inline-flex items-center gap-1.5 text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-medium transition-colors">
                <x-ui.icon name="list" class="w-3.5 h-3.5"/>
                Ver por escalas
            </a>
            @hasanyrole('admin|gerente')
            <a href="{{ route('solicitudes-cotizacion.bajas') }}"
               class="inline-flex items-center gap-1.5 text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-medium transition-colors">
                <x-ui.icon name="x-circle" class="w-3.5 h-3.5"/>
                Auditar bajas
            </a>
            @endhasanyrole
        </div>
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

        <select name="situacion_cliente" onchange="this.form.submit()"
                class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Cliente: todas</option>
            <option value="CLIENTE ASIGNADO" @selected(($filtros['situacion_cliente'] ?? '') === 'CLIENTE ASIGNADO')>Cliente asignado</option>
            <option value="VARIOS" @selected(($filtros['situacion_cliente'] ?? '') === 'VARIOS')>Varios</option>
            <option value="NO REGISTRADO" @selected(($filtros['situacion_cliente'] ?? '') === 'NO REGISTRADO')>No registrado</option>
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
        <p class="text-slate-500 text-sm">Sin conexión al ERP. No se pueden cargar las solicitudes de cotización en este momento.</p>
    </x-ui.card>
    @else

    {{-- Resumen por comercial --}}
    @if($resumen->isNotEmpty())
    <x-ui.card class="overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-slate-900">Resumen por comercial</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead class="bg-gradient-to-b from-slate-50 to-slate-100/70 border-b-2 border-slate-200">
                    <tr class="text-left text-xs font-bold text-slate-700">
                        <th class="px-4 py-2.5">Comercial</th>
                        <th class="px-4 py-2.5 text-right">Solicitudes</th>
                        <th class="px-4 py-2.5 text-right">De clientes</th>
                        <th class="px-4 py-2.5 text-right">Sin asignar</th>
                        <th class="px-4 py-2.5 text-right">Escalas</th>
                        <th class="px-4 py-2.5 text-right">Partes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($resumen as $r)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-2.5 font-medium text-slate-800">{{ $r->nombre_vendedor ?: $r->vendedor }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-800">{{ $r->num_solicitudes }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600">{{ $r->de_clientes }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600">{{ $r->sin_asignar }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600">{{ $r->total_escalas }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600">{{ $r->total_partes }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
    @endif

    {{-- Listado de solicitudes --}}
    @if($solicitudes->isEmpty())
    <x-ui.card class="overflow-hidden">
        <x-ui.empty-state icon="search" title="Sin solicitudes" description="No hay solicitudes de cotización que coincidan con el filtro seleccionado."/>
    </x-ui.card>
    @else
    <x-ui.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead class="bg-gradient-to-b from-slate-50 to-slate-100/70 border-b-2 border-slate-200">
                    <tr class="text-left text-sm font-bold text-slate-700">
                        <th class="px-4 py-3">Nro. Solicitud</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Comercial</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Situación cliente</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Escalas</th>
                        <th class="px-4 py-3 text-right">Partes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($solicitudes as $s)
                    @php
                        $colorSituacion = match($s->situacion_cliente) {
                            'CLIENTE ASIGNADO' => 'green',
                            'VARIOS'           => 'amber',
                            default            => 'slate',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('solicitudes-cotizacion.show', $s->nro_solicitud) }}" class="font-mono text-xs font-semibold text-blue-700 hover:underline">
                                {{ $s->nro_solicitud }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $s->fecha_solicitud?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 whitespace-normal min-w-[140px]">{{ $s->nombre_vendedor ?: $s->vendedor }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900 whitespace-normal min-w-[180px]">
                            {{ $s->cliente ?: $s->cliente_digitado }}
                            @if($s->nit)<p class="text-[11px] text-slate-400 mt-0.5">NIT: {{ $s->nit }}</p>@endif
                        </td>
                        <td class="px-4 py-3"><x-ui.badge :color="$colorSituacion">{{ $s->situacion_cliente }}</x-ui.badge></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">{{ $s->estado }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold text-slate-800">{{ $s->escalas_con_precio }}</span>
                            <span class="text-slate-400">/ {{ $s->escalas }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ $s->partes }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">
        {{ $solicitudes->links() }}
    </div>
    @endif

    @endif

</x-layouts.app>
