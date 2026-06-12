<x-layouts.app title="Mapeo vendedores · CRM" :hide-page-title="true">

<div x-data="{ modalCrear: false, modalEditar: false, editando: null, editandoOriginal: null, codVendedorEditando: '' }">

    {{-- ===== Cabecera ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4 mb-5 flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-base font-semibold text-slate-900 leading-tight">Mapeo de vendedores</h1>
                <p class="text-xs text-slate-400 mt-0.5">Vincula cada asesor del CRM con su código en {{ $compania === 1 ? 'Formacol' : 'Contiflex' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            {{-- Selector de compañía --}}
            <div class="flex gap-1 p-1 rounded-xl bg-slate-100">
                @foreach([1 => 'Formacol', 2 => 'Contiflex'] as $ciaKey => $ciaLabel)
                    <a href="{{ route('mapeo-vendedores.index', ['cia' => $ciaKey]) }}"
                       class="px-3 py-1.5 text-sm rounded-lg font-medium transition {{ $compania === $ciaKey ? 'text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}"
                       style="{{ $compania === $ciaKey ? 'background:'.($ciaKey===1?'#b91c1c':'#1d4ed8') : '' }}">
                        {{ $ciaLabel }}
                    </a>
                @endforeach
            </div>
            <button @click="modalCrear = true"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm">
                <x-ui.icon name="plus" class="w-4 h-4"/>
                Nuevo mapeo
            </button>
        </div>
    </div>

    {{-- ===== Alerta sin ERP ===== --}}
    @if(empty($vendedoresSiesa1) && empty($vendedoresSiesa2))
    <div class="mb-4 px-4 py-3 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-sm">
        ⚠️ No se pudo conectar con SIESA. Los vendedores deberán ingresarse manualmente.
    </div>
    @endif

    {{-- ===== Alertas ===== --}}
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

    {{-- ===== Aviso asesores sin mapeo ===== --}}
    @if($asesores->isNotEmpty())
    <div class="mb-4 px-4 py-3 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl text-sm flex items-center justify-between">
        <span>
            <strong>{{ $asesores->count() }} asesor(es) sin mapear:</strong>
            {{ $asesores->pluck('name')->implode(', ') }}
        </span>
        <button @click="modalCrear = true"
                class="text-blue-700 font-semibold underline text-xs hover:no-underline">
            Mapear ahora →
        </button>
    </div>
    @endif

    {{-- ===== Tabla ===== --}}
    <x-ui.card class="overflow-hidden">
        @if($mapeos->isEmpty())
            <x-ui.empty-state icon="users"
                title="Sin mapeos configurados"
                description="Vincula los asesores del CRM con sus códigos de vendedor en SIESA." />
        @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Asesor CRM</th>
                    <th class="px-5 py-3">Vendedor SIESA</th>
                    <th class="px-5 py-3 text-center">Código</th>
                    <th class="px-5 py-3 text-center">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($mapeos as $m)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold shrink-0">
                                {{ strtoupper(substr($m->asesor?->name ?? 'U', 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">{{ $m->asesor?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $m->asesor?->email ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-slate-700">{{ $m->nombre_vendedor }}</td>
                    <td class="px-5 py-4 text-center">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 font-mono">
                            {{ $m->cod_vendedor_siesa }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        @if($m->activo)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Activo</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="editando = {{ $m->toJson() }}; editandoOriginal = {{ $m->toJson() }}; codVendedorEditando = '{{ trim($m->cod_vendedor_siesa) }}'; modalEditar = true; console.log('[MapeoVendedor] abriendo edición', editando)"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                <x-ui.icon name="edit" class="w-4 h-4"/>
                            </button>
                            <form method="POST" action="{{ route('mapeo-vendedores.destroy', $m) }}"
                                  onsubmit="return confirm('¿Eliminar mapeo de {{ $m->asesor?->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <x-ui.icon name="trash" class="w-4 h-4"/>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </x-ui.card>

    {{-- ===== Modal Crear ===== --}}
    @php
        $vSiesa1Json = collect($vendedoresSiesa1)->map(fn($v) => ['cod' => $v->cod, 'nombre' => $v->nombre])->values()->toJson();
        $vSiesa2Json = collect($vendedoresSiesa2)->map(fn($v) => ['cod' => $v->cod, 'nombre' => $v->nombre])->values()->toJson();
    @endphp
    <div x-show="modalCrear" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="modalCrear = false">
        <template x-if="modalCrear">
        <div @click.outside="modalCrear = false"
             class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6"
             x-data="{
                cia: '',
                siesa1: {{ $vSiesa1Json }},
                siesa2: {{ $vSiesa2Json }},
                cod1: '', nombre1: '',
                cod2: '', nombre2: '',
             }">
            <h3 class="text-base font-bold text-slate-900 mb-5">Nuevo mapeo</h3>
            <form method="POST" action="{{ route('mapeo-vendedores.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="compania" :value="cia"/>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Asesor CRM</label>
                    <select name="asesor_id" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecciona un asesor...</option>
                        @foreach($asesores as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Compañía</label>
                    <select x-model="cia" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecciona...</option>
                        <option value="1">Formacol</option>
                        <option value="2">Contiflex</option>
                        <option value="0">Ambas</option>
                    </select>
                </div>

                {{-- Vendedor Formacol --}}
                <div x-show="cia === '1' || cia === '0'">
                    <label class="block text-xs font-semibold text-red-600 mb-1.5">Vendedor Formacol</label>
                    <select :required="cia === '1' || cia === '0'"
                            @change="const v = siesa1[$event.target.value]; cod1 = v?.cod ?? ''; nombre1 = v?.nombre ?? ''"
                            class="w-full border border-red-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                        <option value="">Selecciona un vendedor...</option>
                        @foreach($vendedoresSiesa1 as $i => $v)
                            <option value="{{ $i }}">{{ $v->cod }} — {{ $v->nombre }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="cod_vendedor_siesa_1" :value="cod1"/>
                    <input type="hidden" name="nombre_vendedor_1" :value="nombre1"/>
                </div>

                {{-- Vendedor Contiflex --}}
                <div x-show="cia === '2' || cia === '0'">
                    <label class="block text-xs font-semibold text-blue-600 mb-1.5">Vendedor Contiflex</label>
                    <select :required="cia === '2'"
                            @change="const v = siesa2[$event.target.value]; cod2 = v?.cod ?? ''; nombre2 = v?.nombre ?? ''"
                            class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Selecciona un vendedor...</option>
                        @foreach($vendedoresSiesa2 as $i => $v)
                            <option value="{{ $i }}">{{ $v->cod }} — {{ $v->nombre }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="cod_vendedor_siesa_2" :value="cod2"/>
                    <input type="hidden" name="nombre_vendedor_2" :value="nombre2"/>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="modalCrear = false"
                            class="flex-1 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
        </template>
    </div>

    {{-- ===== Modal Editar ===== --}}
    <div x-show="modalEditar" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="modalEditar = false; editando = null; editandoOriginal = null">
        <div @click.outside="modalEditar = false; editando = null; editandoOriginal = null"
             class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-base font-bold text-slate-900 mb-1">Editar mapeo</h3>
            <p class="text-sm text-slate-400 mb-5" x-text="editando?.asesor?.name ?? ''"></p>
            <template x-if="editando">
                <form :action="`/mapeo-vendedores/${editando.id}`" method="POST" class="space-y-4"
                      @submit="const fd = new FormData($event.target); console.log('[MapeoVendedor] submit form (alpine)', {id: editando.id, cod: editando.cod_vendedor_siesa, nombre: editando.nombre_vendedor}); console.log('[MapeoVendedor] submit form (DOM real)', {_method: fd.get('_method'), cod: fd.get('cod_vendedor_siesa'), nombre: fd.get('nombre_vendedor'), activo: fd.get('activo')})">
                    @csrf @method('PUT')
                    @if(!empty($vendedoresSiesa1) || !empty($vendedoresSiesa2))
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Vendedor SIESA</label>
                        <select name="_vendedor_select"
                                x-model="codVendedorEditando"
                                @change="editando.cod_vendedor_siesa = $event.target.value; editando.nombre_vendedor = $event.target.selectedOptions[0].dataset.nombre"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($vendedoresSiesa1 as $v)
                                <template x-if="editando.compania == 1">
                                    <option value="{{ trim($v->cod) }}" data-nombre="{{ $v->nombre }}">
                                        {{ trim($v->cod) }} — {{ $v->nombre }}
                                    </option>
                                </template>
                            @endforeach
                            @foreach($vendedoresSiesa2 as $v)
                                <template x-if="editando.compania == 2">
                                    <option value="{{ trim($v->cod) }}" data-nombre="{{ $v->nombre }}">
                                        {{ trim($v->cod) }} — {{ $v->nombre }}
                                    </option>
                                </template>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">
                            Actual: <span class="font-mono font-semibold" x-text="editandoOriginal?.cod_vendedor_siesa"></span>
                            — <span x-text="editandoOriginal?.nombre_vendedor"></span>
                        </p>
                    </div>
                    @endif
                    <input type="hidden" name="cod_vendedor_siesa" :value="editando.cod_vendedor_siesa"/>
                    <input type="hidden" name="nombre_vendedor" :value="editando.nombre_vendedor"/>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="activo" value="0"/>
                            <input type="checkbox" name="activo" value="1" :checked="editando.activo"
                                   class="rounded border-slate-300 text-blue-600"/>
                            <span class="text-sm text-slate-700">Mapeo activo</span>
                        </label>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="modalEditar = false; editando = null; editandoOriginal = null"
                                class="flex-1 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                            Actualizar
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
</x-layouts.app>
