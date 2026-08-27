@php
    $dossier = $solicitud->dossier_erp ?? [];
    $calificacion = $dossier['calificacion'] ?? null;
    $calColor = match($calificacion) {
        'A' => 'green', 'B' => 'yellow', 'C' => 'red', default => 'gray'
    };
    $cartera = collect($dossier['cartera'] ?? [])->sortByDesc('DIAS_VENCIDO')->values();
@endphp
<x-layouts.app title="Solicitud de crédito #{{ $solicitud->id }}">
    <x-slot name="actions">
        <x-ui.button href="{{ route('solicitudes-credito.index') }}" variant="ghost" size="sm">
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
                            <h2 class="text-xl font-semibold text-slate-900">{{ $solicitud->negocio?->nombre_negocio ?? $solicitud->cliente?->razon_social }}</h2>
                            <p class="text-sm text-slate-500 mt-1">
                                {{ $solicitud->cliente?->razon_social }} — NIT {{ $solicitud->cliente?->nit }}
                                @unless($solicitud->negocio)
                                    <span class="text-amber-600">(cupo inicial, sin negocio asociado)</span>
                                @endunless
                            </p>
                        </div>
                        @if($solicitud->pipelineEstado)
                            <x-ui.badge :color="$solicitud->pipelineEstado->color" class="text-sm px-3 py-1">
                                {{ $solicitud->pipelineEstado->nombre }}
                            </x-ui.badge>
                        @endif
                    </div>

                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500 font-medium">Cupo mensual solicitado</dt>
                            <dd class="text-slate-900 font-semibold mt-1">${{ number_format($solicitud->monto_solicitado, 0, ',', '.') }}</dd>
                        </div>
                        @if($solicitud->plazo_solicitado_dias)
                        <div>
                            <dt class="text-slate-500 font-medium">Condiciones de pago</dt>
                            <dd class="text-slate-900 mt-1">{{ $solicitud->plazo_solicitado_dias }} días</dd>
                        </div>
                        @endif
                        @if($solicitud->inventario_consignacion !== null)
                        <div>
                            <dt class="text-slate-500 font-medium">Inventario en consignación</dt>
                            <dd class="text-slate-900 mt-1">{{ $solicitud->inventario_consignacion ? 'Sí' : 'No' }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-slate-500 font-medium">Asesor</dt>
                            <dd class="text-slate-900 mt-1">{{ $solicitud->asesor?->name ?? '—' }}</dd>
                        </div>
                        @if($solicitud->revisor)
                        <div>
                            <dt class="text-slate-500 font-medium">Revisado por</dt>
                            <dd class="text-slate-900 mt-1">{{ $solicitud->revisor->name }} — {{ $solicitud->revisado_en?->format('d/m/Y H:i') }}</dd>
                        </div>
                        @endif
                    </dl>

                    @if($solicitud->referencias_comerciales)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-sm font-medium text-slate-500 mb-2">Referencias comerciales</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($solicitud->referencias_comerciales as $i => $ref)
                            <div class="text-sm bg-slate-50 rounded-lg p-3">
                                <p class="text-xs font-semibold text-slate-500 mb-1">Referencia {{ $i + 1 }}</p>
                                <p class="text-slate-900">{{ $ref['empresa'] ?? '—' }}</p>
                                <p class="text-slate-500 text-xs mt-0.5">{{ $ref['telefono'] ?? '—' }} @if(!empty($ref['nit'])) · NIT {{ $ref['nit'] }} @endif</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($solicitud->justificacion)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-sm font-medium text-slate-500 mb-1">Justificación</p>
                        <p class="text-sm text-slate-700">{{ $solicitud->justificacion }}</p>
                    </div>
                    @endif

                    @if($solicitud->comentario_revision)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-sm font-medium text-slate-500 mb-1">Comentario de revisión</p>
                        <p class="text-sm text-slate-700">{{ $solicitud->comentario_revision }}</p>
                    </div>
                    @endif

                    @if($solicitud->condiciones)
                    <div class="mt-4 pt-4 border-t border-amber-100 bg-amber-50 rounded-lg px-4 py-3">
                        <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide mb-1">Condiciones de aprobación</p>
                        <p class="text-sm text-slate-700">{{ $solicitud->condiciones }}</p>
                    </div>
                    @endif
                </div>
            </x-ui.card>

            {{-- Dossier de crédito (snapshot ERP) --}}
            @if(!empty($dossier))
            <x-ui.card>
                <div class="p-4 border-b border-slate-100 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <h3 class="text-sm font-semibold text-slate-900">Dossier de crédito</h3>
                    <span class="text-xs text-slate-400">
                        foto tomada el {{ \Illuminate\Support\Carbon::parse($dossier['consultado_en'])->format('d/m/Y H:i') }}
                    </span>
                </div>
                <div class="p-4">
                    @if(!empty($dossier['bloqueado']))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                        <p class="text-sm font-semibold text-red-700">Cliente bloqueado en SIESA</p>
                        @if(!empty($dossier['motivo_bloqueo']))
                            <p class="text-xs text-red-600 mt-0.5">{{ $dossier['motivo_bloqueo'] }}</p>
                        @endif
                    </div>
                    @endif

                    <dl class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                        @if($calificacion)
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Calificación</dt>
                            <dd><x-ui.badge :color="$calColor" class="text-sm font-bold">{{ $calificacion }}</x-ui.badge></dd>
                        </div>
                        @endif

                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Cupo de crédito</dt>
                            <dd class="text-sm font-bold text-slate-900">${{ number_format($dossier['cupo_credito'] ?? 0, 0, ',', '.') }}</dd>
                        </div>

                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Cupo disponible</dt>
                            <dd class="text-sm font-bold {{ ($dossier['cupo_disponible'] ?? 0) < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                ${{ number_format($dossier['cupo_disponible'] ?? 0, 0, ',', '.') }}
                            </dd>
                        </div>

                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Cartera total</dt>
                            <dd class="text-sm font-medium text-slate-800">${{ number_format($dossier['saldo_total'] ?? 0, 0, ',', '.') }}</dd>
                        </div>

                        @if(!empty($dossier['condicion_pago']))
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Condición de pago</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $dossier['condicion_pago'] }}</dd>
                        </div>
                        @endif

                        @if(!empty($dossier['tasa_mora']))
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Tasa de mora</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $dossier['tasa_mora'] }}%</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </x-ui.card>
            @endif

            {{-- Cartera vencida --}}
            @if($cartera->isNotEmpty())
            <x-ui.card>
                <div class="p-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">Cartera por cobrar</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Documento</th>
                                <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Vence</th>
                                <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Saldo</th>
                                <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Días vencido</th>
                                <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tramo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($cartera as $doc)
                            <tr>
                                <td class="py-2 px-4 text-slate-700">{{ $doc['TIPO_DOCTO'] ?? '' }} {{ $doc['NUM_DOCTO'] ?? '' }}</td>
                                <td class="py-2 px-4 text-slate-600">{{ $doc['FECHA_VCTO'] ?? '—' }}</td>
                                <td class="py-2 px-4 font-medium text-slate-900">${{ number_format($doc['SALDO'] ?? 0, 0, ',', '.') }}</td>
                                <td class="py-2 px-4 {{ ($doc['DIAS_VENCIDO'] ?? 0) > 0 ? 'text-red-600 font-medium' : 'text-slate-600' }}">{{ $doc['DIAS_VENCIDO'] ?? 0 }}</td>
                                <td class="py-2 px-4 text-slate-600">{{ $doc['TRAMO_AGING'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
            @endif

            {{-- Auditoría --}}
            @if($solicitud->auditoria->isNotEmpty())
            <x-ui.card>
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Historial de cambios</h3>
                    <div class="space-y-3">
                        @foreach($solicitud->auditoria->sortByDesc('created_at') as $audit)
                        <div class="flex gap-3 text-sm">
                            <div class="w-2 h-2 rounded-full mt-1.5 shrink-0
                                @if(str_contains($audit->evento, 'aprobada')) bg-emerald-400
                                @elseif(str_contains($audit->evento, 'rechazada')) bg-red-400
                                @else bg-blue-400
                                @endif
                            "></div>
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

        {{-- Sidebar: decisión de gerencia --}}
        <div class="space-y-4">
            @can('revisar', $solicitud)
                @if(!$solicitud->estaFinalizada())
                <x-ui.card class="p-5"
                    x-data="{
                        loading: false,
                        decision: '',
                        submit() {
                            this.loading = true;
                            const fd = new FormData($refs.form);
                            const body = {};
                            fd.forEach((v, k) => { if (v !== '') body[k] = v; });
                            $api('POST', '/api/solicitudes-credito/{{ $solicitud->id }}/decidir', body)
                                .then(r => {
                                    if (r.success) {
                                        $store.toast.success(r.message);
                                        setTimeout(() => window.location.reload(), 800);
                                    } else {
                                        $store.toast.error(r.message ?? 'Error al procesar la decisión');
                                        this.loading = false;
                                    }
                                }).catch(() => { $store.toast.error('Error de conexión'); this.loading = false; })
                        }
                    }"
                >
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Decisión de gerencia</p>
                    <form x-ref="form" @submit.prevent="submit()" class="space-y-4">
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="decision" value="aprobada" x-model="decision" required>
                                Aprobar
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="decision" value="aprobada_condiciones" x-model="decision">
                                Aprobar con condiciones
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="decision" value="rechazada" x-model="decision">
                                Rechazar
                            </label>
                        </div>

                        <div x-show="decision === 'aprobada_condiciones'" x-cloak>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Condiciones</label>
                            <textarea name="condiciones" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Comentario <span class="text-slate-400 font-normal">(opcional)</span></label>
                            <textarea name="comentario_revision" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                        </div>

                        <x-ui.button type="submit" variant="primary" class="w-full justify-center" x-bind:disabled="loading || !decision">
                            <span x-show="!loading">Confirmar decisión</span>
                            <span x-show="loading">Guardando...</span>
                        </x-ui.button>
                    </form>
                </x-ui.card>
                @endif
            @endcan
        </div>
    </div>
</x-layouts.app>
