<x-layouts.app title="Gestión de registros · Admin">

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Gestión de registros</h2>
        <p class="text-xs text-slate-400 mt-0.5">Eliminar o desactivar negocios y prospectos · Solo administradores</p>
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
    {{ session('success') }}
</div>
@endif

{{-- Tabs --}}
<div class="flex gap-1 mb-5 border-b border-slate-200">
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'negocios', 'page' => 1]) }}"
       class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors
              {{ $tab === 'negocios' ? 'bg-white border border-b-white border-slate-200 text-blue-600 -mb-px' : 'text-slate-500 hover:text-slate-700' }}">
        Negocios
        <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ $tab === 'negocios' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
            {{ $negocios->total() }}
        </span>
    </a>
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'prospectos', 'page' => 1]) }}"
       class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors
              {{ $tab === 'prospectos' ? 'bg-white border border-b-white border-slate-200 text-blue-600 -mb-px' : 'text-slate-500 hover:text-slate-700' }}">
        Prospectos
        <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ $tab === 'prospectos' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
            {{ $prospectos->total() }}
        </span>
    </a>
</div>

{{-- Search --}}
<div class="mb-4" x-data="{ q: '{{ $buscar }}' }">
    <div class="relative max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input type="text" x-model="q"
               @input.debounce.350ms="window.location.href = '{{ request()->fullUrlWithQuery(['tab' => $tab, 'page' => 1]) }}&buscar=' + encodeURIComponent(q)"
               placeholder="Buscar..."
               class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
    </div>
</div>

{{-- ─── NEGOCIOS ─────────────────────────────────────── --}}
@if($tab === 'negocios')
<x-ui.card class="overflow-hidden">
    @if($negocios->isEmpty())
        <x-ui.empty-state icon="briefcase" title="Sin negocios" description="No hay negocios registrados."/>
    @else
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3">Negocio</th>
                <th class="px-5 py-3">Asesor</th>
                <th class="px-5 py-3">Estado pipeline</th>
                <th class="px-5 py-3 text-center">Activo</th>
                <th class="px-5 py-3 text-center">Eliminado</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($negocios as $negocio)
            <tr class="hover:bg-slate-50 transition-colors {{ $negocio->trashed() ? 'opacity-50' : '' }}">
                <td class="px-5 py-3">
                    <p class="font-medium text-slate-900">{{ $negocio->nombre_negocio }}</p>
                    <p class="text-xs text-slate-400 mt-0.5"># {{ $negocio->id }}</p>
                </td>
                <td class="px-5 py-3 text-slate-500 text-xs">{{ $negocio->asesor?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-slate-500 text-xs">{{ $negocio->pipelineEstado?->nombre ?? '—' }}</td>
                <td class="px-5 py-3 text-center">
                    @if(!$negocio->trashed())
                        <span class="inline-block w-2 h-2 rounded-full {{ $negocio->activo ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                        <span class="text-xs text-slate-500 ml-1">{{ $negocio->activo ? 'Sí' : 'No' }}</span>
                    @else
                        <span class="text-xs text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    @if($negocio->trashed())
                        <span class="text-xs text-red-500 font-medium">Eliminado</span>
                    @else
                        <span class="text-xs text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-2">
                        @if($negocio->trashed())
                            {{-- Restaurar --}}
                            <form method="POST" action="{{ route('admin.gestion.negocios.restore', $negocio->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors">
                                    Restaurar
                                </button>
                            </form>
                        @else
                            {{-- Activar / Desactivar --}}
                            <form method="POST" action="{{ route('admin.gestion.negocios.toggle', $negocio) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                                    {{ $negocio->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                            {{-- Eliminar --}}
                            <form method="POST" action="{{ route('admin.gestion.negocios.destroy', $negocio) }}"
                                  onsubmit="return confirm('¿Eliminar el negocio «{{ addslashes($negocio->nombre_negocio) }}»? Podrás restaurarlo después.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Eliminar
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($negocios->hasPages())
    <div class="px-5 py-3 border-t border-slate-100">
        {{ $negocios->links() }}
    </div>
    @endif
    @endif
</x-ui.card>
@endif

{{-- ─── PROSPECTOS ───────────────────────────────────── --}}
@if($tab === 'prospectos')
<x-ui.card class="overflow-hidden">
    @if($prospectos->isEmpty())
        <x-ui.empty-state icon="users" title="Sin prospectos" description="No hay prospectos registrados."/>
    @else
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3">Prospecto / Empresa</th>
                <th class="px-5 py-3">Asesor</th>
                <th class="px-5 py-3">Estado pipeline</th>
                <th class="px-5 py-3 text-center">Activo</th>
                <th class="px-5 py-3 text-center">Eliminado</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($prospectos as $prospecto)
            <tr class="hover:bg-slate-50 transition-colors {{ $prospecto->trashed() ? 'opacity-50' : '' }}">
                <td class="px-5 py-3">
                    <p class="font-medium text-slate-900">{{ $prospecto->empresa }}</p>
                    <p class="text-xs text-slate-400 mt-0.5"># {{ $prospecto->id }}</p>
                </td>
                <td class="px-5 py-3 text-slate-500 text-xs">{{ $prospecto->asesor?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-slate-500 text-xs">{{ $prospecto->estadoPipeline?->nombre ?? '—' }}</td>
                <td class="px-5 py-3 text-center">
                    @if(!$prospecto->trashed())
                        <span class="inline-block w-2 h-2 rounded-full {{ $prospecto->activo ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                        <span class="text-xs text-slate-500 ml-1">{{ $prospecto->activo ? 'Sí' : 'No' }}</span>
                    @else
                        <span class="text-xs text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    @if($prospecto->trashed())
                        <span class="text-xs text-red-500 font-medium">Eliminado</span>
                    @else
                        <span class="text-xs text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-2">
                        @if($prospecto->trashed())
                            {{-- Restaurar --}}
                            <form method="POST" action="{{ route('admin.gestion.prospectos.restore', $prospecto->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors">
                                    Restaurar
                                </button>
                            </form>
                        @else
                            {{-- Activar / Desactivar --}}
                            <form method="POST" action="{{ route('admin.gestion.prospectos.toggle', $prospecto) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                                    {{ $prospecto->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                            {{-- Eliminar --}}
                            <form method="POST" action="{{ route('admin.gestion.prospectos.destroy', $prospecto) }}"
                                  onsubmit="return confirm('¿Eliminar el prospecto «{{ addslashes($prospecto->empresa) }}»? Podrás restaurarlo después.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Eliminar
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($prospectos->hasPages())
    <div class="px-5 py-3 border-t border-slate-100">
        {{ $prospectos->links() }}
    </div>
    @endif
    @endif
</x-ui.card>
@endif

</x-layouts.app>
