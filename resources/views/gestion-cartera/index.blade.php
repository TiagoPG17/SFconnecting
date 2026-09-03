<x-layouts.app title="Gestión de Cartera">

    @php $companias = [1 => 'FORMACOL', 2 => 'CONTIFLEX']; @endphp

    {{-- Filtros --}}
    <form method="GET" class="flex items-center justify-between mb-4 gap-3 flex-wrap">
        <div class="flex items-center gap-2 flex-wrap">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Buscar por número de pedido..."
                       class="w-64 pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
            </div>

            <select name="compania" onchange="this.form.submit()"
                    class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="0" @selected($compania === 0)>Todas las compañías</option>
                <option value="1" @selected($compania === 1)>FORMACOL</option>
                <option value="2" @selected($compania === 2)>CONTIFLEX</option>
            </select>

            <label class="inline-flex items-center gap-2 text-sm text-slate-600 bg-white border border-slate-200 rounded-xl px-3 py-2 cursor-pointer">
                <input type="checkbox" name="solo_hoy" value="1" @checked($soloHoy) onchange="this.form.submit()"
                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-400">
                Solo creados hoy
            </label>

            <button type="submit" class="text-sm px-3 py-2 rounded-xl bg-slate-800 text-white font-medium hover:bg-slate-700 transition-colors">
                Buscar
            </button>
        </div>

        @if($erpDisponible)
        <span class="inline-flex items-center gap-1.5 text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-semibold border border-emerald-200">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            {{ number_format($total, 0, ',', '.') }} pedidos abiertos
        </span>
        @endif
    </form>

    @if(!$erpDisponible)
    <x-ui.card class="p-10 text-center">
        <p class="text-slate-500 text-sm">Sin conexión al ERP. No se pueden cargar los pedidos en este momento.</p>
    </x-ui.card>

    @elseif(empty($pedidos))
    <x-ui.card class="overflow-hidden">
        <x-ui.empty-state icon="search" title="Sin pedidos abiertos" description="No hay pedidos que coincidan con el filtro seleccionado."/>
    </x-ui.card>

    @else
    @php
        $itemsJs = array_map(function ($p) use ($companias) {
            return [
                '_key'              => ($p['Compania'] ?? '').'|'.($p['NroDocumento'] ?? ''),
                'Compania'          => (int) ($p['Compania'] ?? 0),
                'CompaniaLabel'     => $companias[$p['Compania'] ?? 0] ?? 'Compañía '.($p['Compania'] ?? '?'),
                'NroDocumento'      => $p['NroDocumento'] ?? '',
                'Cliente'           => $p['Cliente'] ?? '—',
                'Vendedor'          => $p['Vendedor'] ?? '—',
                'Estado'            => $p['Estado'] ?? '—',
                'OrdenCompra'       => $p['OrdenCompra'] ?? '—',
                'FechaPedido'       => !empty($p['FechaPedido']) ? \Carbon\Carbon::parse($p['FechaPedido'])->format('d/m/Y') : '—',
                'FechaCumplimiento' => !empty($p['FechaCumplimiento']) ? \Carbon\Carbon::parse($p['FechaCumplimiento'])->format('d/m/Y') : '—',
                'UsuarioCreo'       => $p['UsuarioCreo'] ?? '—',
                'FechaCreacion'     => !empty($p['FechaCreacion']) ? \Carbon\Carbon::parse($p['FechaCreacion'])->format('d/m/Y H:i') : '—',
                'CantPedida'        => number_format((float) ($p['CantPedida'] ?? 0), 2, ',', '.'),
                'CantFacturada'     => number_format((float) ($p['CantFacturada'] ?? 0), 2, ',', '.'),
                'CantPendiente'     => number_format((float) ($p['CantPendiente'] ?? 0), 2, ',', '.'),
                'SubtotalPendiente' => number_format((float) ($p['SubtotalPendiente'] ?? 0), 0, ',', '.'),
            ];
        }, $pedidos);
    @endphp
    <x-ui.card class="overflow-hidden" :x-data="'carteraGrid(' . \Illuminate\Support\Js::from($itemsJs) . ')'">
        @if($puedeEditar)
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/50">
            <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-600">
                <input type="checkbox" :checked="todosSeleccionados" @change="toggleTodos()"
                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-400">
                Seleccionar todos
            </label>
            <div class="flex items-center gap-2" x-show="totalSeleccionados > 0">
                <span class="text-xs text-amber-600" x-show="faltanFechas">
                    Falta la fecha en algún pedido seleccionado — mírala en la columna "F. Inicio Aviso".
                </span>
                <button type="button" @click="notificar()" :disabled="totalSeleccionados === 0 || enviando || faltanFechas"
                        class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    <x-ui.icon name="check" class="w-3.5 h-3.5"/>
                    <span x-text="enviando ? 'Enviando…' : 'Notificar seleccionados (' + totalSeleccionados + ')'"></span>
                </button>
            </div>
        </div>
        @else
        <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-100 bg-amber-50/60 text-xs text-amber-700">
            <x-ui.icon name="eye" class="w-3.5 h-3.5"/>
            Modo solo lectura — tu rol no permite seleccionar ni notificar pedidos.
        </div>
        @endif
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead class="bg-gradient-to-b from-slate-50 to-slate-100/70 border-b-2 border-slate-200">
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        @if($puedeEditar)
                        <th class="px-4 py-3 w-8"></th>
                        @endif
                        <th class="px-4 py-3">Compañía</th>
                        <th class="px-4 py-3">Nro. Documento</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Vendedor</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Orden Compra</th>
                        <th class="px-4 py-3">F. Pedido</th>
                        <th class="px-4 py-3">F. Cumplimiento</th>
                        @if($puedeEditar)
                        <th class="px-4 py-3">F. Inicio Aviso</th>
                        @endif
                        <th class="px-4 py-3">Creó</th>
                        <th class="px-4 py-3">F. Creación</th>
                        <th class="px-4 py-3 text-right">Cant. Pedida</th>
                        <th class="px-4 py-3 text-right">Cant. Facturada</th>
                        <th class="px-4 py-3 text-right">Cant. Pendiente</th>
                        <th class="px-4 py-3 text-right">Subtotal Pendiente</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="item in items" :key="item._key">
                    <tr class="hover:bg-slate-50 transition-colors" :class="{ 'bg-blue-50/50': estaSeleccionado(item) }">
                        @if($puedeEditar)
                        <td class="px-4 py-3">
                            <input type="checkbox" :checked="estaSeleccionado(item)" @change="toggle(item)"
                                   class="rounded border-slate-300 text-blue-600 focus:ring-blue-400">
                        </td>
                        @endif
                        <td class="px-4 py-3">
                            <span class="text-xs font-semibold text-slate-500" x-text="item.CompaniaLabel"></span>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-800" x-text="item.NroDocumento"></td>
                        <td class="px-4 py-3 font-medium text-slate-900 whitespace-normal min-w-[180px]" x-text="item.Cliente"></td>
                        <td class="px-4 py-3 text-slate-500" x-text="item.Vendedor"></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600" x-text="item.Estado"></span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500" x-text="item.OrdenCompra"></td>
                        <td class="px-4 py-3 text-xs text-slate-500" x-text="item.FechaPedido"></td>
                        <td class="px-4 py-3 text-xs text-slate-500" x-text="item.FechaCumplimiento"></td>
                        @if($puedeEditar)
                        <td class="px-4 py-3">
                            <template x-if="estaSeleccionado(item)">
                                <input type="date" x-model="seleccionados[item._key].fecha_inicio_cobro"
                                       :class="!seleccionados[item._key].fecha_inicio_cobro ? 'border-amber-400' : 'border-slate-200'"
                                       class="text-xs border rounded-lg px-2 py-1 w-32 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            </template>
                            <template x-if="!estaSeleccionado(item)">
                                <span class="text-xs text-slate-300">—</span>
                            </template>
                        </td>
                        @endif
                        <td class="px-4 py-3 text-xs text-slate-500" x-text="item.UsuarioCreo"></td>
                        <td class="px-4 py-3 text-xs text-slate-500" x-text="item.FechaCreacion"></td>
                        <td class="px-4 py-3 text-right text-slate-600" x-text="item.CantPedida"></td>
                        <td class="px-4 py-3 text-right text-slate-600" x-text="item.CantFacturada"></td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-800" x-text="item.CantPendiente"></td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-800" x-text="'$' + item.SubtotalPendiente"></td>
                    </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </x-ui.card>

    {{-- Paginación --}}
    @if($totalPaginas > 1)
    <div class="mt-4 flex items-center justify-between">
        <p class="text-xs text-slate-400">
            Página {{ $pagina }} de {{ $totalPaginas }} · {{ number_format($total, 0, ',', '.') }} pedidos en total
        </p>
        <div class="flex items-center gap-1">
            @if($pagina > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => $pagina - 1]) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                    Anterior
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-100 text-slate-300 cursor-not-allowed">
                    Anterior
                </span>
            @endif

            @php
                $desde = max(1, $pagina - 2);
                $hasta = min($totalPaginas, $pagina + 2);
            @endphp
            @if($desde > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}"
                   class="inline-flex items-center justify-center w-8 h-8 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">1</a>
                @if($desde > 2)<span class="px-1 text-slate-300 text-xs">…</span>@endif
            @endif
            @for($i = $desde; $i <= $hasta; $i++)
                @if($i === $pagina)
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs rounded-lg bg-blue-600 text-white font-semibold">{{ $i }}</span>
                @else
                    <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                       class="inline-flex items-center justify-center w-8 h-8 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">{{ $i }}</a>
                @endif
            @endfor
            @if($hasta < $totalPaginas)
                @if($hasta < $totalPaginas - 1)<span class="px-1 text-slate-300 text-xs">…</span>@endif
                <a href="{{ request()->fullUrlWithQuery(['page' => $totalPaginas]) }}"
                   class="inline-flex items-center justify-center w-8 h-8 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">{{ $totalPaginas }}</a>
            @endif

            @if($pagina < $totalPaginas)
                <a href="{{ request()->fullUrlWithQuery(['page' => $pagina + 1]) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                    Siguiente
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-100 text-slate-300 cursor-not-allowed">
                    Siguiente
                </span>
            @endif
        </div>
    </div>
    @endif
    @endif

    {{-- Notificaciones pendientes de envío --}}
    <div class="mt-8">
        <div class="flex items-center gap-2 mb-3">
            <x-ui.icon name="clock" class="w-4 h-4 text-slate-400"/>
            <h2 class="text-sm font-bold text-slate-900">Notificaciones pendientes de envío</h2>
            <span class="text-xs text-slate-400">({{ count($pendientes) }})</span>
        </div>

        @if(empty($pendientes))
        <x-ui.card class="p-6 text-center">
            <p class="text-sm text-slate-400">No hay notificaciones de cartera pendientes de envío.</p>
        </x-ui.card>
        @else
        <x-ui.card class="overflow-hidden" x-data="carteraPendientes()">
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead class="bg-gradient-to-b from-slate-50 to-slate-100/70 border-b-2 border-slate-200">
                        <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="px-4 py-3">Compañía</th>
                            <th class="px-4 py-3">Nro. Documento</th>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Vendedor</th>
                            <th class="px-4 py-3">F. Pedido</th>
                            <th class="px-4 py-3">F. Cumplimiento</th>
                            <th class="px-4 py-3">Empezar a avisar</th>
                            <th class="px-4 py-3 text-right">Subtotal Pendiente</th>
                            <th class="px-4 py-3">Registrado</th>
                            @if($puedeEditar)
                            <th class="px-4 py-3"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendientes as $n)
                        @php
                            $compania = (int) ($n['Compania'] ?? 0);
                            $nroDocumento = $n['NroDocumento'] ?? '';
                            $key = $compania.'|'.$nroDocumento;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors" x-show="!resueltos.includes('{{ $key }}')" x-cloak>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-500">{{ $companias[$compania] ?? 'Compañía '.$compania }}</td>
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-800">{{ $nroDocumento ?: '—' }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900 whitespace-normal min-w-[180px]">{{ $n['Cliente'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $n['Vendedor'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ !empty($n['FechaPedido']) ? \Carbon\Carbon::parse($n['FechaPedido'])->format('d/m/Y') : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ !empty($n['FechaCumplimiento']) ? \Carbon\Carbon::parse($n['FechaCumplimiento'])->format('d/m/Y') : '—' }}</td>
                            <td class="px-4 py-3 text-xs font-semibold text-blue-700">{{ !empty($n['FechaInicioCobro']) ? \Carbon\Carbon::parse($n['FechaInicioCobro'])->format('d/m/Y') : '—' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">${{ number_format((float) ($n['SubtotalPendiente'] ?? 0), 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ !empty($n['FechaRegistro']) ? \Carbon\Carbon::parse($n['FechaRegistro'])->format('d/m/Y H:i') : '—' }}</td>
                            @if($puedeEditar)
                            <td class="px-4 py-3">
                                <button type="button"
                                        @click="resolver({{ $compania }}, {{ Illuminate\Support\Js::from($nroDocumento) }})"
                                        :disabled="estaProcesando('{{ $key }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                                    <x-ui.icon name="check" class="w-3.5 h-3.5"/>
                                    <span x-text="estaProcesando('{{ $key }}') ? 'Marcando…' : 'Ya pagado / resuelto'"></span>
                                </button>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
        @endif
    </div>

@push('scripts')
<script>
    function carteraGrid(items) {
        return {
            items,
            seleccionados: {},
            enviando: false,
            toggle(item) {
                if (this.seleccionados[item._key]) {
                    delete this.seleccionados[item._key];
                } else {
                    this.seleccionados[item._key] = { compania: item.Compania, nro_documento: item.NroDocumento, fecha_inicio_cobro: '' };
                }
            },
            estaSeleccionado(item) {
                return !!this.seleccionados[item._key];
            },
            get totalSeleccionados() {
                return Object.keys(this.seleccionados).length;
            },
            get todosSeleccionados() {
                return this.items.length > 0 && this.totalSeleccionados === this.items.length;
            },
            get faltanFechas() {
                return Object.values(this.seleccionados).some(p => !p.fecha_inicio_cobro);
            },
            toggleTodos() {
                if (this.todosSeleccionados) {
                    this.seleccionados = {};
                } else {
                    const nuevos = {};
                    this.items.forEach(item => {
                        nuevos[item._key] = this.seleccionados[item._key]
                            ?? { compania: item.Compania, nro_documento: item.NroDocumento, fecha_inicio_cobro: '' };
                    });
                    this.seleccionados = nuevos;
                }
            },
            async notificar() {
                const pedidos = Object.values(this.seleccionados);
                if (!pedidos.length || this.enviando) return;
                if (pedidos.some(p => !p.fecha_inicio_cobro)) {
                    $store.toast.error('Cada pedido seleccionado necesita su propia fecha de inicio de aviso.');
                    return;
                }
                this.enviando = true;
                try {
                    const res = await fetch('{{ route('gestion-cartera.notificar') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ pedidos }),
                    });
                    const data = await res.json();
                    if (!data.success) {
                        $store.toast.error(data.message || 'No se pudo notificar.');
                        this.enviando = false;
                        return;
                    }
                    $store.toast.success(
                        data.insertados > 0
                            ? `${data.insertados} pedido(s) enviados a notificación.`
                            : 'Los pedidos seleccionados ya estaban notificados.'
                    );
                    this.seleccionados = {};
                    setTimeout(() => window.location.reload(), 900);
                } catch (e) {
                    $store.toast.error('Error de conexión al notificar.');
                    this.enviando = false;
                }
            },
        };
    }

    function carteraPendientes() {
        return {
            resueltos: [],
            procesando: [],
            estaProcesando(key) {
                return this.procesando.includes(key);
            },
            async resolver(compania, nroDocumento) {
                const key = compania + '|' + nroDocumento;
                if (this.procesando.includes(key)) return;
                this.procesando.push(key);
                try {
                    const res = await fetch('{{ route('gestion-cartera.resolver') }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ compania: Number(compania), nro_documento: nroDocumento }),
                    });
                    const data = await res.json();
                    if (!data.success) {
                        $store.toast.error(data.message || 'No se pudo marcar como resuelto.');
                    } else {
                        this.resueltos.push(key);
                        $store.toast.success('Pedido marcado como resuelto.');
                    }
                } catch (e) {
                    $store.toast.error('Error de conexión al marcar como resuelto.');
                }
                this.procesando = this.procesando.filter((k) => k !== key);
            },
        };
    }
</script>
@endpush

</x-layouts.app>
