@php
    $filas = $cliente->datos_carga ?? [];
    $campos = config('cliente_datos_carga_campos');
@endphp
<x-layouts.app title="Contabilidad — {{ $cliente->razon_social }}">
    <x-slot name="actions">
        <x-ui.button href="{{ route('contabilidad.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">
            {{-- Cabecera --}}
            <x-ui.card>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">{{ $cliente->razon_social }}</h2>
                            <p class="text-sm text-slate-500 mt-1">NIT {{ $cliente->nit }} — {{ $cliente->companiaNombre() ?? '—' }}</p>
                        </div>
                        @if($cliente->pendienteContabilidad())
                            <x-ui.badge color="yellow">Pendiente</x-ui.badge>
                        @else
                            <x-ui.badge color="green">Registrado en SIESA</x-ui.badge>
                        @endif
                    </div>

                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500 font-medium">Comercial asignado</dt>
                            <dd class="text-slate-900 mt-1">{{ $cliente->asesor?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 font-medium">Ciudad</dt>
                            <dd class="text-slate-900 mt-1">{{ $cliente->ciudad ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 font-medium">Dirección</dt>
                            <dd class="text-slate-900 mt-1">{{ $cliente->direccion ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 font-medium">Fecha de conversión</dt>
                            <dd class="text-slate-900 mt-1">{{ $cliente->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if(!$cliente->pendienteContabilidad())
                        <div>
                            <dt class="text-slate-500 font-medium">Registrado por</dt>
                            <dd class="text-slate-900 mt-1">{{ $cliente->revisadoContabilidadPor?->name }} — {{ $cliente->revisado_contabilidad_en?->format('d/m/Y H:i') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </x-ui.card>

            {{-- Datos cargados por el comercial --}}
            @if(!empty($filas))
            <x-ui.card x-data="{ activo: 0 }">
                <div class="p-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">Información del cliente cargada por el comercial</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ count($filas) }} registro(s)</p>
                </div>

                @if(count($filas) > 1)
                <div class="flex items-center gap-1 border-b border-slate-200 overflow-x-auto px-4">
                    @foreach($filas as $i => $fila)
                    <button type="button" @click="activo = {{ $i }}"
                            :class="activo === {{ $i }} ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                            class="shrink-0 px-3 py-2 text-xs font-semibold border-b-2 transition-colors whitespace-nowrap">
                        {{ $fila['razon_social'] ?? ('Registro ' . ($i + 1)) }}
                    </button>
                    @endforeach
                </div>
                @endif

                @foreach($filas as $i => $fila)
                <div x-show="activo === {{ $i }}" @if($i > 0) x-cloak @endif class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($campos as $campo)
                    <div>
                        <dt class="text-xs font-medium text-slate-500">{{ $campo['label'] }}</dt>
                        <dd class="text-sm mt-0.5 {{ empty($fila[$campo['key']]) ? 'text-amber-600 italic' : 'text-slate-800' }}">
                            {{ $fila[$campo['key']] ?? 'Sin completar' }}
                        </dd>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </x-ui.card>
            @endif

            {{-- Contactos --}}
            @if($cliente->contactos->isNotEmpty())
            <x-ui.card>
                <div class="p-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">Contactos</h3>
                </div>
                <div class="p-4 space-y-2">
                    @foreach($cliente->contactos as $contacto)
                    <div class="text-sm">
                        <span class="font-medium text-slate-800">{{ $contacto->nombre }}</span>
                        <span class="text-slate-400 ml-1.5">{{ $contacto->cargo }}</span>
                    </div>
                    @endforeach
                </div>
            </x-ui.card>
            @endif
        </div>

        {{-- Sidebar: acción de contabilidad --}}
        <div class="space-y-4">
            @if($cliente->pendienteContabilidad())
            <x-ui.card class="p-5"
                x-data="{
                    loading: false,
                    marcar() {
                        this.loading = true;
                        $api('POST', '/api/clientes/{{ $cliente->id }}/marcar-registrado-contabilidad', {})
                            .then(r => {
                                if (r.success) {
                                    $store.toast.success(r.message);
                                    setTimeout(() => window.location.href = '{{ route('contabilidad.index') }}', 800);
                                } else {
                                    $store.toast.error(r.message ?? 'Error al marcar el cliente');
                                    this.loading = false;
                                }
                            }).catch(() => { $store.toast.error('Error de conexión'); this.loading = false; })
                    }
                }"
            >
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Registro en SIESA</p>
                <p class="text-sm text-slate-600 mb-4">Esta información es solo de consulta. Registra y asigna el comercial directamente en SIESA y luego confirma aquí.</p>
                <x-ui.button variant="primary" class="w-full justify-center" @click="marcar()" x-bind:disabled="loading">
                    <span x-show="!loading">Ya registrado en SIESA</span>
                    <span x-show="loading">Guardando...</span>
                </x-ui.button>
            </x-ui.card>
            @endif
        </div>
    </div>
</x-layouts.app>
