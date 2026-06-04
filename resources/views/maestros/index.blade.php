<x-layouts.app title="Maestros CRM">

@php
$tiposLabel = [
    'tipo_negocio'   => 'Tipos de negocio',
    'prioridad'      => 'Prioridades',
    'fuente_lead'    => 'Fuentes de lead',
    'motivo_perdida' => 'Motivos de pérdida',
    'tipo_actividad' => 'Tipos de actividad',
    'clasificacion'  => 'Clasificaciones',
    'segmento'       => 'Segmentos',
];
@endphp

<div class="space-y-6">

    {{-- ═══ MAESTROS COMERCIALES ═══ --}}
    @foreach($tiposLabel as $tipo => $label)
    <x-ui.card padding="none" x-data="{
        modal: false,
        modo: 'crear',
        form: { nombre: '', tipo: '{{ $tipo }}', color: '#6366f1', icono: '', orden: '' },
        editId: null,
        loading: false,
        abrirCrear() { this.modo='crear'; this.form={ nombre:'', tipo:'{{ $tipo }}', color:'#6366f1', icono:'', orden:'' }; this.editId=null; this.modal=true; },
        abrirEditar(item) { this.modo='editar'; this.form={ nombre:item.nombre, tipo:item.tipo, color:item.color||'#6366f1', icono:item.icono||'', orden:item.orden }; this.editId=item.id; this.modal=true; },
        async guardar() {
            this.loading = true;
            try {
                const method = this.modo==='crear' ? 'POST' : 'PUT';
                const url = this.modo==='crear' ? '/api/maestros/comercial' : '/api/maestros/comercial/'+this.editId;
                const res = await $api(method, url, this.form);
                if (res.success) { $store.toast.success(res.message); this.modal=false; setTimeout(()=>location.reload(),600); }
                else { const m=Object.values(res.errors??{}).flat(); $store.toast.error(m[0]??res.message); }
            } finally { this.loading=false; }
        },
        async toggle(id, activo) {
            const res = await $api('PATCH', '/api/maestros/comercial/'+id+'/toggle');
            if (res.success) { $store.toast.success(res.message); setTimeout(()=>location.reload(),600); }
            else $store.toast.error(res.message);
        }
    }">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">{{ $label }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ isset($maestros[$tipo]) ? $maestros[$tipo]->count() : 0 }} registros
                </p>
            </div>
            @if($puedeGestionar)
            <button @click="abrirCrear()"
                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <x-ui.icon name="plus" class="w-3.5 h-3.5"/> Agregar
            </button>
            @endif
        </div>

        <div class="divide-y divide-slate-50">
            @if(isset($maestros[$tipo]) && $maestros[$tipo]->count())
                @foreach($maestros[$tipo]->sortBy('orden') as $item)
                <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors">
                    @if($item->color)
                        <div class="w-2.5 h-2.5 rounded-full shrink-0 ring-1 ring-black/10"
                             style="background-color: {{ $item->color }}"></div>
                    @else
                        <div class="w-2.5 h-2.5 rounded-full shrink-0 bg-slate-200"></div>
                    @endif
                    <span class="text-sm flex-1 {{ $item->activo ? 'text-slate-800' : 'text-slate-400 line-through' }}">
                        {{ $item->nombre }}
                    </span>
                    <span class="text-xs text-slate-300 font-mono hidden sm:block">{{ $item->slug }}</span>
                    @if(!$item->activo)
                        <x-ui.badge color="slate">Inactivo</x-ui.badge>
                    @endif
                    @if($puedeGestionar)
                    <div class="flex items-center gap-1 shrink-0">
                        <button @click="abrirEditar({ id:{{ $item->id }}, nombre:'{{ addslashes($item->nombre) }}', tipo:'{{ $item->tipo }}', color:'{{ $item->color ?? '#6366f1' }}', icono:'{{ $item->icono ?? '' }}', orden:{{ $item->orden }} })"
                            class="p-1 text-slate-400 hover:text-blue-600 rounded transition-colors" title="Editar">
                            <x-ui.icon name="edit" class="w-3.5 h-3.5"/>
                        </button>
                        <button @click="toggle({{ $item->id }}, {{ $item->activo ? 'true' : 'false' }})"
                            class="p-1 text-slate-400 hover:text-{{ $item->activo ? 'amber' : 'emerald' }}-600 rounded transition-colors"
                            title="{{ $item->activo ? 'Desactivar' : 'Activar' }}">
                            <x-ui.icon name="{{ $item->activo ? 'x-circle' : 'check-circle' }}" class="w-3.5 h-3.5"/>
                        </button>
                    </div>
                    @endif
                </div>
                @endforeach
            @else
                <div class="px-4 py-6 text-center text-xs text-slate-400">Sin registros</div>
            @endif
        </div>

        {{-- Modal crear/editar maestro --}}
        @if($puedeGestionar)
        <div x-show="modal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/40" @click="modal=false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-5"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <h3 class="text-sm font-semibold text-slate-900 mb-4"
                    x-text="modo==='crear' ? 'Agregar {{ $label }}' : 'Editar {{ $label }}'"></h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-medium text-slate-700 block mb-1">Nombre <span class="text-red-500">*</span></label>
                        <x-ui.input x-model="form.nombre" placeholder="Nombre del registro"/>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <label class="text-xs font-medium text-slate-700 block mb-1">Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="form.color"
                                       class="w-8 h-8 rounded cursor-pointer border-0 p-0.5 bg-transparent">
                                <x-ui.input x-model="form.color" placeholder="#6366f1" class="font-mono text-xs"/>
                            </div>
                        </div>
                        <div class="w-20">
                            <label class="text-xs font-medium text-slate-700 block mb-1">Orden</label>
                            <x-ui.input type="number" x-model="form.orden" placeholder="0" class="text-center"/>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5 pt-4 border-t border-slate-100">
                    <button @click="modal=false"
                        class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button @click="guardar()" :disabled="loading"
                        class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                        <span x-text="loading ? 'Guardando…' : (modo==='crear' ? 'Crear' : 'Guardar')"></span>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </x-ui.card>
    @endforeach

    {{-- ═══ PIPELINE ESTADOS ═══ --}}
    @foreach(['prospecto' => 'Pipeline Prospectos', 'negocio' => 'Pipeline Negocios'] as $tipoPipeline => $labelPipeline)
    <x-ui.card padding="none" x-data="{
        modal: false,
        modo: 'crear',
        form: { nombre:'', tipo:'{{ $tipoPipeline }}', color:'#3b82f6', porcentaje_cierre:0, es_final:false, es_ganado:false, es_perdido:false, orden:'' },
        editId: null,
        loading: false,
        abrirCrear() { this.modo='crear'; this.form={ nombre:'', tipo:'{{ $tipoPipeline }}', color:'#3b82f6', porcentaje_cierre:0, es_final:false, es_ganado:false, es_perdido:false, orden:'' }; this.editId=null; this.modal=true; },
        abrirEditar(e) { this.modo='editar'; this.form={ nombre:e.nombre, tipo:e.tipo, color:e.color||'#3b82f6', porcentaje_cierre:e.porcentaje_cierre, es_final:e.es_final, es_ganado:e.es_ganado, es_perdido:e.es_perdido, orden:e.orden }; this.editId=e.id; this.modal=true; },
        async guardar() {
            this.loading=true;
            try {
                const method = this.modo==='crear' ? 'POST' : 'PUT';
                const url = this.modo==='crear' ? '/api/maestros/pipeline-estado' : '/api/maestros/pipeline-estado/'+this.editId;
                const res = await $api(method, url, this.form);
                if (res.success) { $store.toast.success(res.message); this.modal=false; setTimeout(()=>location.reload(),600); }
                else { const m=Object.values(res.errors??{}).flat(); $store.toast.error(m[0]??res.message); }
            } finally { this.loading=false; }
        },
        async toggle(id) {
            const res = await $api('PATCH', '/api/maestros/pipeline-estado/'+id+'/toggle');
            if (res.success) { $store.toast.success(res.message); setTimeout(()=>location.reload(),600); }
            else $store.toast.error(res.message);
        }
    }">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">{{ $labelPipeline }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ isset($pipeline[$tipoPipeline]) ? $pipeline[$tipoPipeline]->count() : 0 }} estados
                </p>
            </div>
            @if($puedeGestionar)
            <button @click="abrirCrear()"
                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <x-ui.icon name="plus" class="w-3.5 h-3.5"/> Agregar estado
            </button>
            @endif
        </div>

        <div class="divide-y divide-slate-50">
            @if(isset($pipeline[$tipoPipeline]) && $pipeline[$tipoPipeline]->count())
                @foreach($pipeline[$tipoPipeline]->sortBy('orden') as $estado)
                <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors">
                    <div class="w-2.5 h-2.5 rounded-full shrink-0 ring-1 ring-black/10"
                         style="background-color: {{ $estado->color ?? '#94a3b8' }}"></div>
                    <span class="text-sm flex-1 {{ $estado->activo ? 'text-slate-800' : 'text-slate-400 line-through' }}">
                        {{ $estado->nombre }}
                    </span>
                    <span class="text-xs text-slate-400 font-mono tabular-nums">{{ $estado->porcentaje_cierre }}%</span>
                    @if($estado->es_ganado)
                        <x-ui.badge color="green">Ganado</x-ui.badge>
                    @elseif($estado->es_perdido)
                        <x-ui.badge color="red">Perdido</x-ui.badge>
                    @elseif($estado->es_final)
                        <x-ui.badge color="slate">Final</x-ui.badge>
                    @endif
                    @if(!$estado->activo)
                        <x-ui.badge color="slate">Inactivo</x-ui.badge>
                    @endif
                    @if($puedeGestionar)
                    <div class="flex items-center gap-1 shrink-0">
                        <button @click="abrirEditar({ id:{{ $estado->id }}, nombre:'{{ addslashes($estado->nombre) }}', tipo:'{{ $estado->tipo }}', color:'{{ $estado->color ?? '#3b82f6' }}', porcentaje_cierre:{{ $estado->porcentaje_cierre }}, es_final:{{ $estado->es_final ? 'true' : 'false' }}, es_ganado:{{ $estado->es_ganado ? 'true' : 'false' }}, es_perdido:{{ $estado->es_perdido ? 'true' : 'false' }}, orden:{{ $estado->orden }} })"
                            class="p-1 text-slate-400 hover:text-blue-600 rounded transition-colors" title="Editar">
                            <x-ui.icon name="edit" class="w-3.5 h-3.5"/>
                        </button>
                        <button @click="toggle({{ $estado->id }})"
                            class="p-1 text-slate-400 hover:text-{{ $estado->activo ? 'amber' : 'emerald' }}-600 rounded transition-colors"
                            title="{{ $estado->activo ? 'Desactivar' : 'Activar' }}">
                            <x-ui.icon name="{{ $estado->activo ? 'x-circle' : 'check-circle' }}" class="w-3.5 h-3.5"/>
                        </button>
                    </div>
                    @endif
                </div>
                @endforeach
            @else
                <div class="px-4 py-6 text-center text-xs text-slate-400">Sin estados configurados</div>
            @endif
        </div>

        {{-- Modal pipeline estado --}}
        @if($puedeGestionar)
        <div x-show="modal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/40" @click="modal=false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-5"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <h3 class="text-sm font-semibold text-slate-900 mb-4"
                    x-text="modo==='crear' ? 'Nuevo estado — {{ $labelPipeline }}' : 'Editar estado'"></h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-medium text-slate-700 block mb-1">Nombre <span class="text-red-500">*</span></label>
                        <x-ui.input x-model="form.nombre" placeholder="Ej: Propuesta enviada"/>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <label class="text-xs font-medium text-slate-700 block mb-1">Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="form.color"
                                       class="w-8 h-8 rounded cursor-pointer border-0 p-0.5 bg-transparent">
                                <x-ui.input x-model="form.color" placeholder="#3b82f6" class="font-mono text-xs"/>
                            </div>
                        </div>
                        <div class="w-24">
                            <label class="text-xs font-medium text-slate-700 block mb-1">% Cierre</label>
                            <x-ui.input type="number" x-model="form.porcentaje_cierre" min="0" max="100" placeholder="0"/>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                            <input type="checkbox" x-model="form.es_ganado" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            Ganado
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                            <input type="checkbox" x-model="form.es_perdido" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                            Perdido
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                            <input type="checkbox" x-model="form.es_final" class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                            Final
                        </label>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-700 block mb-1">Orden</label>
                        <x-ui.input type="number" x-model="form.orden" placeholder="Auto" class="w-24"/>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5 pt-4 border-t border-slate-100">
                    <button @click="modal=false"
                        class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button @click="guardar()" :disabled="loading"
                        class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                        <span x-text="loading ? 'Guardando…' : (modo==='crear' ? 'Crear estado' : 'Guardar')"></span>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </x-ui.card>
    @endforeach

</div>
</x-layouts.app>
