<x-layouts.app title="Clientes huérfanos · CRM">

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Clientes huérfanos</h2>
            <p class="text-xs text-slate-400 mt-0.5">
                Clientes de Contiflex sin compra en más de 365 días · CIA {{ $compania }}
                @if($esAsesor) <span class="text-blue-500">(solo tu compañía)</span> @endif
            </p>
        </div>
        @if($erpDisponible)
        <span class="inline-flex items-center gap-1.5 text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-semibold border border-emerald-200">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            {{ $totalHuerfanos }} disponibles
            @if($totalHuerfanos > count($huerfanos))
                <span class="text-emerald-500 font-normal">(mostrando {{ count($huerfanos) }})</span>
            @endif
        </span>
        @endif
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
    {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
    {{ $errors->first() }}
</div>
@endif

@if(!$erpDisponible)
<x-ui.card class="p-10 text-center">
    <p class="text-slate-500 text-sm">Sin conexión al ERP. No se pueden cargar los clientes huérfanos.</p>
</x-ui.card>

@elseif(empty($huerfanos))
<x-ui.card class="overflow-hidden">
    <x-ui.empty-state icon="users" title="Sin clientes huérfanos" description="No hay clientes sin asesor asignado en este momento."/>
</x-ui.card>

@else
@php
    $huerfanosJs = array_map(function ($c) use ($compania) {
        return [
            'NIT'                      => $c['NIT'] ?? '',
            'RAZON_SOCIAL'             => $c['RAZON_SOCIAL'] ?? '',
            'CIUDAD'                   => $c['CIUDAD'] ?? '',
            'COMPANIA'                 => $compania === 1 ? 'Formacol' : 'Contiflex',
            'NOMBRE_VENDEDOR'          => $c['NOMBRE_VENDEDOR'] ?? '',
            'MOTIVO_HUERFANO'          => $c['MOTIVO_HUERFANO'] ?? 'Sin vendedor',
            'VLR_NETO_FACTURADO'       => (float) ($c['VLR_NETO_FACTURADO'] ?? 0),
            'DIAS_DESDE_ULTIMA_COMPRA' => (int) ($c['DIAS_DESDE_ULTIMA_COMPRA'] ?? 0),
            'ULTIMA_FACTURA'           => $c['ULTIMA_FACTURA'] ? \Carbon\Carbon::parse($c['ULTIMA_FACTURA'])->format('d/m/Y') : '',
        ];
    }, $huerfanos);
@endphp

<div x-data="huerfanosTable(@js($huerfanosJs))">

    {{-- Search --}}
    <div class="mb-3">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input x-model="search" type="text" placeholder="Buscar por nombre, NIT o ciudad..."
                   class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        </div>
    </div>

    <x-ui.card class="overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3 cursor-pointer select-none hover:text-slate-800 transition-colors" @click="sortOn('RAZON_SOCIAL')">
                        Cliente <span x-text="arrow('RAZON_SOCIAL')"></span>
                    </th>
                    <th class="px-5 py-3 cursor-pointer select-none hover:text-slate-800 transition-colors" @click="sortOn('CIUDAD')">
                        Ciudad <span x-text="arrow('CIUDAD')"></span>
                    </th>
                    <th class="px-5 py-3 cursor-pointer select-none hover:text-slate-800 transition-colors" @click="sortOn('COMPANIA')">
                        Compañía <span x-text="arrow('COMPANIA')"></span>
                    </th>
                    <th class="px-5 py-3 text-right cursor-pointer select-none hover:text-slate-800 transition-colors" @click="sortOn('VLR_NETO_FACTURADO')">
                        Facturación histórica <span x-text="arrow('VLR_NETO_FACTURADO')"></span>
                    </th>
                    <th class="px-5 py-3 cursor-pointer select-none hover:text-slate-800 transition-colors" @click="sortOn('NOMBRE_VENDEDOR')">
                        Último vendedor <span x-text="arrow('NOMBRE_VENDEDOR')"></span>
                    </th>
                    <th class="px-5 py-3 cursor-pointer select-none hover:text-slate-800 transition-colors" @click="sortOn('MOTIVO_HUERFANO')">
                        Motivo <span x-text="arrow('MOTIVO_HUERFANO')"></span>
                    </th>
                    <th class="px-5 py-3 text-center cursor-pointer select-none hover:text-slate-800 transition-colors" @click="sortOn('DIAS_DESDE_ULTIMA_COMPRA')">
                        Días sin comprar <span x-text="arrow('DIAS_DESDE_ULTIMA_COMPRA')"></span>
                    </th>
                    <th class="px-5 py-3 text-center cursor-pointer select-none hover:text-slate-800 transition-colors" @click="sortOn('ULTIMA_FACTURA')">
                        Última compra <span x-text="arrow('ULTIMA_FACTURA')"></span>
                    </th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-if="filtered.length === 0">
                    <tr>
                        <td colspan="9" class="px-5 py-10 text-center text-sm text-slate-400">
                            Sin resultados para "<span x-text="search"></span>"
                        </td>
                    </tr>
                </template>
                <template x-for="c in filtered" :key="c.NIT">
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-900" x-text="c.RAZON_SOCIAL"></p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5" x-text="c.NIT"></p>
                        </td>
                        <td class="px-5 py-4 text-slate-500 text-xs" x-text="c.CIUDAD || '—'"></td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                  :class="c.COMPANIA === 'Formacol' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'"
                                  x-text="c.COMPANIA">
                            </span>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-500" x-text="c.NOMBRE_VENDEDOR || '—'"></td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                  :class="{
                                      'bg-red-100 text-red-700':   c.MOTIVO_HUERFANO === 'Sin vendedor',
                                      'bg-blue-100 text-blue-700': c.MOTIVO_HUERFANO === 'Con vendedor',
                                  }"
                                  x-text="c.MOTIVO_HUERFANO">
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right font-semibold text-slate-800"
                            x-text="'$' + c.VLR_NETO_FACTURADO.toLocaleString('es-CO', {maximumFractionDigits:0})"></td>
                        <td class="px-5 py-4 text-center">
                            <span class="font-bold"
                                  :class="c.DIAS_DESDE_ULTIMA_COMPRA > 180 ? 'text-red-600' : (c.DIAS_DESDE_ULTIMA_COMPRA > 90 ? 'text-amber-600' : 'text-slate-600')"
                                  x-text="c.DIAS_DESDE_ULTIMA_COMPRA"></span>
                            <span class="text-xs text-slate-400 ml-0.5">días</span>
                        </td>
                        <td class="px-5 py-4 text-center text-xs text-slate-400" x-text="c.ULTIMA_FACTURA || '—'"></td>
                        <td class="px-5 py-4 text-right">
                            <form method="POST"
                                  :action="`{{ url('clientes-huerfanos') }}/${c.NIT}/reclamar`"
                                  :onsubmit="`return confirm('¿Tomar a ' + c.RAZON_SOCIAL + ' para tu cartera?')`">
                                @csrf
                                <input type="hidden" name="razon_social" :value="c.RAZON_SOCIAL">
                                <input type="hidden" name="ciudad" :value="c.CIUDAD">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Tomar cliente
                                </button>
                            </form>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        <div class="px-5 py-2 border-t border-slate-100 text-xs text-slate-400" x-show="search">
            <span x-text="filtered.length"></span> resultado(s) en esta página
        </div>
    </x-ui.card>

    {{-- Paginación --}}
    @if($totalPaginas > 1)
    <div class="mt-4 flex items-center justify-between">
        <p class="text-xs text-slate-400">
            Página {{ $pagina }} de {{ $totalPaginas }}
            · {{ $totalHuerfanos }} clientes en total
        </p>
        <div class="flex items-center gap-1">
            {{-- Anterior --}}
            @if($pagina > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => $pagina - 1]) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Anterior
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-100 text-slate-300 cursor-not-allowed">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Anterior
                </span>
            @endif

            {{-- Números de página --}}
            @php
                $desde = max(1, $pagina - 2);
                $hasta = min($totalPaginas, $pagina + 2);
            @endphp
            @if($desde > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}"
                   class="inline-flex items-center justify-center w-8 h-8 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">1</a>
                @if($desde > 2)
                    <span class="px-1 text-slate-300 text-xs">…</span>
                @endif
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
                @if($hasta < $totalPaginas - 1)
                    <span class="px-1 text-slate-300 text-xs">…</span>
                @endif
                <a href="{{ request()->fullUrlWithQuery(['page' => $totalPaginas]) }}"
                   class="inline-flex items-center justify-center w-8 h-8 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">{{ $totalPaginas }}</a>
            @endif

            {{-- Siguiente --}}
            @if($pagina < $totalPaginas)
                <a href="{{ request()->fullUrlWithQuery(['page' => $pagina + 1]) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                    Siguiente
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-100 text-slate-300 cursor-not-allowed">
                    Siguiente
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            @endif
        </div>
    </div>
    @endif
</div>

<script>
function huerfanosTable(items) {
    return {
        all: items,
        search: '',
        sortBy: 'VLR_NETO_FACTURADO',
        sortDir: -1,
        get filtered() {
            const q = this.search.toLowerCase().trim();
            let rows = q
                ? this.all.filter(c =>
                    (c.RAZON_SOCIAL || '').toLowerCase().includes(q) ||
                    (c.NIT || '').toLowerCase().includes(q) ||
                    (c.CIUDAD || '').toLowerCase().includes(q)
                )
                : [...this.all];
            if (this.sortBy) {
                rows.sort((a, b) => {
                    const va = a[this.sortBy] ?? '';
                    const vb = b[this.sortBy] ?? '';
                    if (typeof va === 'number') return (va - vb) * this.sortDir;
                    return String(va).localeCompare(String(vb), 'es') * this.sortDir;
                });
            }
            return rows;
        },
        sortOn(field) {
            this.sortBy === field ? this.sortDir *= -1 : (this.sortBy = field, this.sortDir = 1);
        },
        arrow(field) {
            return this.sortBy !== field ? '↕' : this.sortDir === 1 ? '↑' : '↓';
        },
    };
}
</script>
@endif

</x-layouts.app>
