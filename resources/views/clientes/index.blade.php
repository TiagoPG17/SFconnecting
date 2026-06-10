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

            <x-ui.card class="p-4 mb-4" x-data="{ search: '{{ request('buscar') }}' }">
                <div class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-48">
                        <div class="relative">
                            <x-ui.icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                            <input type="text" x-model="search"
                                @keydown.enter="window.location.href = '{{ route('clientes.index') }}?buscar=' + encodeURIComponent(search)"
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

        <x-ui.card class="p-4 mb-4"
            x-data="{
                search: '{{ request('buscar') }}',
                estado: '{{ request('estado') }}',
                goFilter() {
                    let url = new URL(window.location.href);
                    this.search ? url.searchParams.set('buscar', this.search) : url.searchParams.delete('buscar');
                    this.estado ? url.searchParams.set('estado', this.estado) : url.searchParams.delete('estado');
                    window.location.href = url.toString();
                }
            }"
        >
            <div class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-48">
                    <div class="relative">
                        <x-ui.icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                        <input type="text" x-model="search" @keydown.enter="goFilter()"
                            placeholder="Buscar razón social, NIT, email..."
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <select x-model="estado" @change="goFilter()"
                    class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los estados</option>
                    <option value="activo"    {{ request('estado') === 'activo'    ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo"  {{ request('estado') === 'inactivo'  ? 'selected' : '' }}>Inactivo</option>
                    <option value="prospecto" {{ request('estado') === 'prospecto' ? 'selected' : '' }}>Prospecto</option>
                </select>
                @if(request()->hasAny(['buscar', 'estado']))
                    <x-ui.button href="{{ route('clientes.index') }}" variant="ghost" size="sm">
                        <x-ui.icon name="x" class="w-4 h-4"/> Limpiar
                    </x-ui.button>
                @endif
            </div>
        </x-ui.card>

        <x-ui.card>
            @if($clientes->isEmpty())
                <x-ui.empty-state icon="users" title="Sin clientes"
                    description="Registra tu primer cliente o conviértelo desde un prospecto.">
                    <x-slot name="action">
                        <x-ui.button href="{{ route('clientes.create') }}" variant="primary" size="sm">
                            <x-ui.icon name="plus" class="w-4 h-4"/> Crear cliente
                        </x-ui.button>
                    </x-slot>
                </x-ui.empty-state>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Razón social</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">NIT</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Contacto</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Ciudad</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asesor</th>
                            <th class="py-3 px-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($clientes as $c)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 font-medium text-slate-900">{{ $c->razon_social }}</td>
                            <td class="py-3 px-4 text-slate-600 font-mono text-xs">{{ $c->nit }}</td>
                            <td class="py-3 px-4">
                                @if($c->email)<p class="text-slate-700">{{ $c->email }}</p>@endif
                                @if($c->telefono)<p class="text-xs text-slate-400">{{ $c->telefono }}</p>@endif
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $c->ciudad ?? '—' }}</td>
                            <td class="py-3 px-4">
                                <x-ui.badge :color="match($c->estado) { 'activo' => 'green', 'inactivo' => 'red', default => 'yellow' }">
                                    {{ ucfirst($c->estado) }}
                                </x-ui.badge>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $c->asesor?->name ?? '—' }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1 justify-end">
                                    <x-ui.button href="{{ route('clientes.show', $c) }}" variant="ghost" size="xs">
                                        <x-ui.icon name="eye" class="w-4 h-4"/>
                                    </x-ui.button>
                                    <x-ui.button href="{{ route('clientes.edit', $c) }}" variant="ghost" size="xs">
                                        <x-ui.icon name="edit" class="w-4 h-4"/>
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($clientes->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $clientes->withQueryString()->links() }}
                </div>
            @endif
            @endif
        </x-ui.card>

    @endif
</x-layouts.app>
