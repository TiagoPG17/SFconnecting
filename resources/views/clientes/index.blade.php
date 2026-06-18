<x-layouts.app title="Clientes">
    <x-slot name="actions">
        @can('create', App\Domain\Clientes\Models\Cliente::class)
        <x-ui.button href="{{ route('clientes.create') }}" variant="primary" size="sm">
            <x-ui.icon name="plus" class="w-4 h-4"/> Nuevo cliente
        </x-ui.button>
        @endcan
    </x-slot>

    {{-- ── MODO ERP: lista desde SIESA (comercial) ── --}}
    @if($esModoErp)

        @if($sinMapeoErp ?? false)
            <x-ui.card>
                <x-ui.empty-state icon="users" title="Sin mapeo SIESA"
                    description="Tu usuario no tiene un vendedor SIESA asignado. Contacta al administrador para configurarlo."/>
            </x-ui.card>
        @else

            <x-ui.card class="p-4 mb-4" x-data="{ search: '{{ request('buscar') }}', buscando: false }">
                <div class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-48">
                        <div class="relative">
                            <x-ui.icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" x-show="!buscando"/>
                            <svg x-show="buscando" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <input type="text" x-model="search"
                                @input="buscando = true"
                                @input.debounce.800ms="window.location.href = '{{ route('clientes.index') }}?buscar=' + encodeURIComponent(search)"
                                placeholder="Buscar razón social o NIT..."
                                class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    @if(request('buscar'))
                        <x-ui.button href="{{ route('clientes.index') }}" variant="ghost" size="sm">
                            <x-ui.icon name="x" class="w-4 h-4"/> Limpiar
                        </x-ui.button>
                    @endif
                </div>
            </x-ui.card>

            <x-ui.card>
                @if(empty($clientes))
                    <x-ui.empty-state icon="users" title="Sin clientes"
                        description="{{ request('buscar') ? 'No hay resultados para la búsqueda.' : 'No tienes clientes asignados en SIESA.' }}"/>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Razón social</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">NIT</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Ciudad</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Facturado año</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Días sin compra</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                                <th class="py-3 px-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($clientes as $c)
                            @php
                                $horizonte = $c['HORIZONTE_PRESUPUESTO'] ?? '';
                                [$badgeColor, $badgeLabel] = match(true) {
                                    str_starts_with($horizonte, 'P1') => ['green',  'Activo'],
                                    str_starts_with($horizonte, 'P2') => ['yellow', 'En riesgo'],
                                    str_starts_with($horizonte, 'P3') => ['red',    'Recuperar'],
                                    str_starts_with($horizonte, 'P4') => ['gray',   'Inactivo'],
                                    default                           => ['gray',   'Sin datos'],
                                };
                                $dias = (int) ($c['DIAS_DESDE_ULTIMA_COMPRA'] ?? 0);
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-4 font-medium text-slate-900">{{ $c['RAZON_SOCIAL'] ?? '—' }}</td>
                                <td class="py-3 px-4 text-slate-600 font-mono text-xs">{{ $c['NIT'] ?? '—' }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $c['CIUDAD'] ?? '—' }}</td>
                                <td class="py-3 px-4 text-right text-slate-700 font-medium">
                                    ${{ number_format($c['FACTURADO_ANIO_ACTUAL'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="{{ $dias > 60 ? 'text-rose-600 font-semibold' : 'text-slate-600' }}">
                                        {{ $dias }} días
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <x-ui.badge :color="$badgeColor">{{ $badgeLabel }}</x-ui.badge>
                                </td>
                                <td class="py-3 px-4">
                                    <x-ui.button href="{{ route('clientes.erp.show', $c['NIT']) }}" variant="ghost" size="xs">
                                        <x-ui.icon name="eye" class="w-4 h-4"/>
                                    </x-ui.button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @php
                    $totalPaginas = (int) ceil($total / $porPagina);
                    $paginaActual = (int) $pagina;
                @endphp
                @if($totalPaginas > 1)
                <div class="flex items-center justify-between px-4 py-3 border-t border-slate-100">
                    <p class="text-xs text-slate-500">
                        {{ ($paginaActual - 1) * $porPagina + 1 }}–{{ min($paginaActual * $porPagina, $total) }}
                        de {{ $total }} clientes
                    </p>
                    <div class="flex items-center gap-1.5">
                        @if($paginaActual > 1)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $paginaActual - 1]) }}"
                               class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                ← Anterior
                            </a>
                        @endif
                        @if($paginaActual < $totalPaginas)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $paginaActual + 1]) }}"
                               class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                Siguiente →
                            </a>
                        @endif
                    </div>
                </div>
                @endif
                @endif
            </x-ui.card>
        @endif

    {{-- ── MODO LOCAL: lista desde DB (admin/gerente/comercial) ── --}}
    @else

        @if($esComercial ?? false)
        <div x-data="{
            cargando: false,
            mensaje: '',
            exito: false,
            async sincronizar() {
                this.cargando = true;
                this.mensaje  = '';
                try {
                    const res  = await fetch('{{ route('clientes.sincronizar') }}', {
                        method:  'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    this.exito  = data.success;
                    this.mensaje = data.message;
                    if (data.success) { setTimeout(() => window.location.reload(), 1800); }
                } catch {
                    this.exito  = false;
                    this.mensaje = 'Error de red. Intenta de nuevo.';
                } finally {
                    this.cargando = false;
                }
            }
        }" class="mb-4">
            <x-ui.card class="p-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Cartera SIESA</p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            @if($sinMapeoErp ?? false)
                                Tu usuario no tiene vendedor SIESA asignado. Contacta al administrador.
                            @else
                                Importa o actualiza tus clientes desde SIESA para registrar seguimientos y contactos.
                            @endif
                        </p>
                    </div>
                    @if(!($sinMapeoErp ?? false))
                    <button @click="sincronizar()" :disabled="cargando"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
                        <svg x-show="!cargando" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <svg x-show="cargando" class="w-4 h-4 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span x-text="cargando ? 'Cargando...' : 'Cargar mi cartera'"></span>
                    </button>
                    @endif
                </div>
                <div x-show="mensaje" x-cloak
                    class="mt-3 px-3 py-2 rounded-lg text-sm"
                    :class="exito ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                    x-text="mensaje">
                </div>
            </x-ui.card>
        </div>
        @endif

        @php
            $initialItems = $clientes->map(fn ($c) => [
                'id'          => $c->id,
                'razon_social'=> $c->razon_social,
                'nit'         => $c->nit,
                'email'       => $c->email ?? '',
                'telefono'    => $c->telefono ?? '',
                'ciudad'      => $c->ciudad ?? '',
                'estado'      => $c->estado,
                'asesor'      => $c->asesor?->name ?? '—',
                'url_show'    => route('clientes.show', $c),
                'url_edit'    => route('clientes.edit', $c),
            ])->values()->toArray();

            $initialMeta = [
                'total'        => $clientes->total(),
                'current_page' => $clientes->currentPage(),
                'last_page'    => $clientes->lastPage(),
                'per_page'     => $clientes->perPage(),
            ];
        @endphp

        <div x-data="clienteSearch(@js($initialItems), @js($initialMeta), '{{ route('clientes.index') }}')">

            {{-- Barra de búsqueda --}}
            <x-ui.card class="p-4 mb-4">
                <div class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-48 relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" x-show="!cargando" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                        <svg x-show="cargando" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <input type="text" x-model="q"
                               @input.debounce.200ms="buscar()"
                               placeholder="Buscar razón social, NIT, email..."
                               class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <select x-model="estado" @change="buscar()"
                        class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="prospecto">Prospecto</option>
                    </select>
                    <button x-show="q || estado" @click="limpiar()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Limpiar
                    </button>
                </div>
            </x-ui.card>

            {{-- Tabla --}}
            <x-ui.card class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide cursor-pointer select-none hover:text-slate-800 transition-colors"
                                    @click="ordenar('razon_social')">
                                    Razón social <span x-text="flecha('razon_social')" class="ml-0.5"></span>
                                </th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide cursor-pointer select-none hover:text-slate-800 transition-colors"
                                    @click="ordenar('nit')">
                                    NIT <span x-text="flecha('nit')" class="ml-0.5"></span>
                                </th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide cursor-pointer select-none hover:text-slate-800 transition-colors"
                                    @click="ordenar('ciudad')">
                                    Ciudad <span x-text="flecha('ciudad')" class="ml-0.5"></span>
                                </th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide cursor-pointer select-none hover:text-slate-800 transition-colors"
                                    @click="ordenar('estado')">
                                    Estado <span x-text="flecha('estado')" class="ml-0.5"></span>
                                </th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide cursor-pointer select-none hover:text-slate-800 transition-colors"
                                    @click="ordenar('asesor')">
                                    Asesor <span x-text="flecha('asesor')" class="ml-0.5"></span>
                                </th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-if="sorted.length === 0 && !cargando">
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-sm text-slate-400">Sin resultados</td>
                                </tr>
                            </template>
                            <template x-for="c in sorted" :key="c.id">
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 font-medium text-slate-900" x-text="c.razon_social"></td>
                                    <td class="py-3 px-4 text-slate-600 font-mono text-xs" x-text="c.nit"></td>
                                    <td class="py-3 px-4 text-slate-600 text-xs" x-text="c.ciudad || '—'"></td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                              :class="{
                                                  'bg-green-100 text-green-700':  c.estado === 'activo',
                                                  'bg-red-100 text-red-700':      c.estado === 'inactivo',
                                                  'bg-yellow-100 text-yellow-700': c.estado === 'prospecto',
                                              }"
                                              x-text="c.estado.charAt(0).toUpperCase() + c.estado.slice(1)">
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 text-xs" x-text="c.asesor"></td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-1 justify-end">
                                            <a :href="c.url_show"
                                               class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a :href="c.url_edit"
                                               class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between" x-show="meta.last_page > 1">
                    <p class="text-xs text-slate-500">
                        <span x-text="meta.total"></span> clientes ·
                        página <span x-text="meta.current_page"></span> de <span x-text="meta.last_page"></span>
                    </p>
                    <div class="flex items-center gap-1">
                        <button @click="irPagina(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                                class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            ← Anterior
                        </button>
                        <button @click="irPagina(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                                class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            Siguiente →
                        </button>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <script>
        function clienteSearch(initialItems, initialMeta, baseUrl) {
            return {
                items:    initialItems,
                meta:     initialMeta,
                q:        '',
                estado:   '',
                cargando: false,
                _abort:   null,
                sortBy:   '',
                sortDir:  1,

                get sorted() {
                    if (!this.sortBy) return this.items;
                    const field = this.sortBy;
                    const dir   = this.sortDir;
                    return [...this.items].sort((a, b) => {
                        const va = (a[field] ?? '').toString().toLowerCase();
                        const vb = (b[field] ?? '').toString().toLowerCase();
                        return va < vb ? -dir : va > vb ? dir : 0;
                    });
                },

                ordenar(field) {
                    if (this.sortBy === field) {
                        this.sortDir *= -1;
                    } else {
                        this.sortBy  = field;
                        this.sortDir = 1;
                    }
                },

                flecha(field) {
                    if (this.sortBy !== field) return '↕';
                    return this.sortDir === 1 ? '↑' : '↓';
                },

                async buscar(pagina = 1) {
                    if (this._abort) this._abort.abort();
                    this._abort   = new AbortController();
                    this.cargando = true;
                    try {
                        const url = new URL(baseUrl, window.location.origin);
                        if (this.q)      url.searchParams.set('buscar', this.q);
                        if (this.estado) url.searchParams.set('estado', this.estado);
                        url.searchParams.set('page', pagina);

                        const res  = await fetch(url.toString(), {
                            signal:  this._abort.signal,
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const data = await res.json();
                        this.items = data.items;
                        this.meta  = data.meta;
                    } catch (e) {
                        if (e.name !== 'AbortError') console.error('Error al buscar clientes:', e);
                    } finally {
                        this.cargando = false;
                    }
                },

                irPagina(n) {
                    if (n < 1 || n > this.meta.last_page) return;
                    this.buscar(n);
                },

                limpiar() {
                    this.q      = '';
                    this.estado = '';
                    this.buscar();
                },
            };
        }
        </script>

    @endif
</x-layouts.app>
