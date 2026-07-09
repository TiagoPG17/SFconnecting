<x-layouts.app :title="'Prospecto: ' . $prospecto->empresa">
    <x-slot name="actions">
        <x-ui.button href="{{ route('prospectos.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
        @can('update', $prospecto)
        <x-ui.button href="{{ route('prospectos.edit', $prospecto) }}" variant="secondary" size="sm">
            <x-ui.icon name="edit" class="w-4 h-4"/> Editar
        </x-ui.button>
        @endcan
        @can('convertir', $prospecto)
        @if(!$prospecto->estaConvertido())
        <x-ui.button variant="primary" size="sm" @click="$dispatch('open-convertir')">
            <x-ui.icon name="check" class="w-4 h-4"/> Convertir en cliente
        </x-ui.button>
        @endif
        @endcan
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Info principal --}}
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">{{ $prospecto->empresa }}</h2>
                            <p class="text-sm text-slate-500 mt-1">{{ $prospecto->codigo }}</p>
                        </div>
                        @if($prospecto->estadoPipeline)
                            <x-ui.badge :color="$prospecto->estadoPipeline->color" class="text-sm px-3 py-1">
                                {{ $prospecto->estadoPipeline->nombre }}
                            </x-ui.badge>
                        @endif
                    </div>

                    @if($prospecto->estaConvertido())
                        <div class="mb-4 flex items-center gap-2 px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">
                            <x-ui.icon name="check" class="w-4 h-4"/>
                            Convertido en cliente el {{ $prospecto->fecha_conversion?->format('d/m/Y') }}
                        </div>
                    @endif

                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500 font-medium">Contacto</dt>
                            <dd class="text-slate-900 mt-1">{{ $prospecto->contacto }}</dd>
                        </div>
                        @if($prospecto->email)
                        <div>
                            <dt class="text-slate-500 font-medium">Email</dt>
                            <dd class="text-slate-900 mt-1">{{ $prospecto->email }}</dd>
                        </div>
                        @endif
                        @if($prospecto->telefono)
                        <div>
                            <dt class="text-slate-500 font-medium">Teléfono</dt>
                            <dd class="text-slate-900 mt-1">{{ $prospecto->telefono }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-slate-500 font-medium">Asesor</dt>
                            <dd class="text-slate-900 mt-1">{{ $prospecto->asesor?->name ?? '—' }}</dd>
                        </div>
                        @if($prospecto->origen)
                        <div>
                            <dt class="text-slate-500 font-medium">Origen</dt>
                            <dd class="text-slate-900 mt-1">{{ $prospecto->origen->nombre }}</dd>
                        </div>
                        @endif
                        @if($prospecto->prioridad)
                        <div>
                            <dt class="text-slate-500 font-medium">Prioridad</dt>
                            <dd class="mt-1">
                                <x-ui.badge :color="$prospecto->prioridad->color">
                                    {{ $prospecto->prioridad->nombre }}
                                </x-ui.badge>
                            </dd>
                        </div>
                        @endif
                        @if($prospecto->fecha_proximo_contacto)
                        <div>
                            <dt class="text-slate-500 font-medium">Próximo contacto</dt>
                            <dd class="text-slate-900 mt-1">{{ $prospecto->fecha_proximo_contacto->format('d/m/Y') }}</dd>
                        </div>
                        @endif
                    </dl>

                    @if($prospecto->observaciones)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-sm font-medium text-slate-500 mb-1">Observaciones</p>
                        <p class="text-sm text-slate-700">{{ $prospecto->observaciones }}</p>
                    </div>
                    @endif
                </div>
            </x-ui.card>

            {{-- Seguimientos --}}
            <x-ui.card
                x-data="{
                    open: false,
                    form: { tipo: 'llamada', resultado: 'exitoso', descripcion: '', fecha_seguimiento: '', proxima_fecha: '' },
                    loading: false,
                    submit() {
                        this.loading = true;
                        const payload = { ...this.form, prospecto_id: {{ $prospecto->id }} };
                        if (!payload.proxima_fecha) delete payload.proxima_fecha;
                        $api('POST', '/api/seguimientos', payload)
                            .then(r => {
                                if (r.success) { $store.toast.success('Seguimiento registrado'); setTimeout(() => location.reload(), 600); }
                                else { $store.toast.error(r.message ?? 'Error'); this.loading = false; }
                            })
                            .catch(() => { $store.toast.error('Error al guardar'); this.loading = false; });
                    }
                }"
            >
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Seguimientos</h3>
                    <button @click="open = true"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Registrar seguimiento
                    </button>
                </div>

                @if($seguimientos->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-400">Sin seguimientos registrados</div>
                @else
                <div class="divide-y divide-slate-100">
                    @foreach($seguimientos as $seg)
                    <div class="px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <x-ui.badge color="blue" class="text-xs">{{ $seg->tipo }}</x-ui.badge>
                                    <x-ui.badge color="{{ $seg->resultado === 'exitoso' ? 'green' : ($seg->resultado === 'no_contactado' ? 'red' : 'yellow') }}" class="text-xs">
                                        {{ ucfirst(str_replace('_', ' ', $seg->resultado)) }}
                                    </x-ui.badge>
                                </div>
                                <p class="text-sm text-slate-700">{{ $seg->descripcion }}</p>
                                @if($seg->proxima_fecha)
                                    <p class="text-xs text-slate-500 mt-1">
                                        Próximo: {{ $seg->proxima_fecha->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-slate-400">{{ $seg->fecha_seguimiento->format('d/m/Y') }}</p>
                                <p class="text-xs text-slate-500">{{ $seg->asesor?->name }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Modal nuevo seguimiento --}}
                <x-ui.modal title="Nuevo seguimiento">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Tipo</label>
                                <select x-model="form.tipo" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2">
                                    @foreach(['llamada','reunion','email','visita','whatsapp','otro'] as $t)
                                    <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Resultado</label>
                                <select x-model="form.resultado" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2">
                                    <option value="exitoso">Exitoso</option>
                                    <option value="no_contactado">No contactado</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Nota</label>
                            <textarea x-model="form.descripcion" rows="3"
                                class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 resize-none"
                                placeholder="Mínimo 10 caracteres…"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <x-ui.input type="datetime-local" label="Fecha seguimiento" x-model="form.fecha_seguimiento"/>
                            <x-ui.input type="datetime-local" label="Próxima fecha (opcional)" x-model="form.proxima_fecha"/>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <x-ui.button type="button" variant="ghost" @click="open = false">Cancelar</x-ui.button>
                            <x-ui.button type="button" variant="primary" @click="submit()" x-bind:disabled="loading">
                                <span x-show="!loading">Guardar</span>
                                <span x-show="loading">Guardando…</span>
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.modal>
            </x-ui.card>

            {{-- Auditoría --}}
            @if($prospecto->auditoria->isNotEmpty())
            <x-ui.card>
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Historial de cambios</h3>
                    <div class="space-y-3">
                        @foreach($prospecto->auditoria->sortByDesc('created_at') as $audit)
                        <div class="flex gap-3 text-sm">
                            <div class="w-2 h-2 rounded-full bg-blue-400 mt-1.5 shrink-0"></div>
                            <div>
                                <p class="text-slate-700">
                                    <span class="font-medium">{{ $audit->usuario?->name ?? 'Sistema' }}</span>
                                    — {{ ucfirst(str_replace('_', ' ', $audit->evento)) }}
                                    @if($audit->estado_anterior && $audit->estado_nuevo)
                                        : <span class="text-slate-500">{{ $audit->estado_anterior }}</span>
                                        → <span class="text-slate-900 font-medium">{{ $audit->estado_nuevo }}</span>
                                    @endif
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $audit->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </x-ui.card>
            @endif
        </div>

        {{-- Sidebar métricas --}}
        <div class="space-y-4">
            <x-ui.card class="p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Valor estimado</p>
                <p class="text-2xl font-bold text-slate-900">
                    {{ $prospecto->valor_estimado ? '$' . number_format($prospecto->valor_estimado, 0, ',', '.') : '—' }}
                </p>
            </x-ui.card>

            <x-ui.card class="p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Probabilidad cierre</p>
                <p class="text-2xl font-bold text-slate-900 mb-2">{{ $prospecto->probabilidadEfectiva() }}%</p>
                <x-ui.progress :value="$prospecto->probabilidadEfectiva()" size="sm"/>
            </x-ui.card>

            <x-ui.card class="p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Seguimientos</p>
                <p class="text-2xl font-bold text-slate-900">{{ $seguimientos->count() }}</p>
            </x-ui.card>

            @if($prospecto->estaConvertido() && $prospecto->clienteConvertido)
            <x-ui.card class="p-5">
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-2">Cliente</p>
                <p class="font-medium text-slate-900">{{ $prospecto->clienteConvertido->razon_social ?? $prospecto->clienteConvertido->nombre ?? '—' }}</p>
                <p class="text-xs text-slate-500 mt-1">Convertido por {{ $prospecto->convertidoPor?->name }}</p>
            </x-ui.card>
            @endif
        </div>
    </div>

    {{-- Modal convertir --}}
    @can('convertir', $prospecto)
    @if(!$prospecto->estaConvertido())
    <div x-data="{ open: false }" @open-convertir.window="open = true">
        <x-ui.modal title="Convertir en cliente">
            <form
                x-data="{ loading: false }"
                @submit.prevent="
                    loading = true;
                    $api('POST', '/api/prospectos/{{ $prospecto->id }}/convertir', {
                            razon_social: $el.razon_social.value,
                            nit: $el.nit.value,
                        }).then(r => {
                        if (r.success) { $store.toast.success(r.message); setTimeout(() => location.reload(), 800); }
                        else { $store.toast.error(r.message); loading = false; }
                    }).catch(() => { $store.toast.error('Error al convertir'); loading = false; })
                "
                class="space-y-4"
            >
                <x-ui.input name="razon_social" label="Razón social" :value="$prospecto->empresa" required/>
                <x-ui.input name="nit" label="NIT / Identificación" placeholder="Opcional"/>
                <div class="flex justify-end gap-3 pt-2">
                    <x-ui.button type="button" variant="ghost" @click="open = false">Cancelar</x-ui.button>
                    <x-ui.button type="submit" variant="primary" x-bind:disabled="loading">
                        <span x-show="!loading">Confirmar conversión</span>
                        <span x-show="loading">Procesando...</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>
    @endif
    @endcan
</x-layouts.app>
