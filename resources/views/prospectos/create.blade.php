<x-layouts.app title="Nuevo prospecto">
    <x-slot name="actions">
        <x-ui.button href="{{ route('prospectos.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
    </x-slot>

    <div class="max-w-2xl mx-auto" x-data="{
        form: {
            empresa: '', contacto: '', email: '', telefono: '',
            estado_pipeline_id: '', origen_id: '', prioridad_id: '',
            valor_estimado: '', probabilidad_cierre: '',
            fecha_proximo_contacto: '', observaciones: ''
        },
        errors: {},
        loading: false,
        async guardar() {
            this.errors = {};
            this.loading = true;
            try {
                const body = Object.fromEntries(
                    Object.entries(this.form).filter(([, v]) => v !== '')
                );
                const r = await $api('POST', '/api/prospectos', body);
                if (r.success) {
                    $store.toast.success(r.message);
                    setTimeout(() => window.location.href = '{{ route('prospectos.index') }}', 800);
                } else {
                    this.errors = r.errors ?? {};
                    const msgs = Object.values(this.errors).flat();
                    $store.toast.error(
                        msgs.length > 1
                            ? `Corrige los ${msgs.length} errores marcados.`
                            : (msgs[0] ?? r.message ?? 'Error al crear')
                    );
                }
            } catch {
                $store.toast.error('Error de conexión');
            } finally {
                this.loading = false;
            }
        }
    }">
        <x-ui.card>
            <div class="p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-6">Datos del prospecto</h2>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Empresa <span class="text-red-500">*</span>
                            </label>
                            <x-ui.input
                                x-model="form.empresa"
                                @input="delete errors.empresa"
                                x-error="errors.empresa"
                                placeholder="Nombre de la empresa"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Contacto principal <span class="text-red-500">*</span>
                            </label>
                            <x-ui.input
                                x-model="form.contacto"
                                @input="delete errors.contacto"
                                x-error="errors.contacto"
                                placeholder="Nombre del contacto"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                            <x-ui.input
                                type="email"
                                x-model="form.email"
                                @input="delete errors.email"
                                x-error="errors.email"
                                placeholder="correo@empresa.com"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                            <x-ui.input
                                x-model="form.telefono"
                                @input="delete errors.telefono"
                                x-error="errors.telefono"
                                placeholder="+57 300 000 0000"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Estado pipeline <span class="text-red-500">*</span>
                            </label>
                            <x-ui.select
                                x-model="form.estado_pipeline_id"
                                @change="delete errors.estado_pipeline_id"
                                x-error="errors.estado_pipeline_id">
                                <option value="">Seleccionar estado...</option>
                                @foreach($estados as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Origen</label>
                            <x-ui.select
                                x-model="form.origen_id"
                                @change="delete errors.origen_id"
                                x-error="errors.origen_id">
                                <option value="">Sin origen</option>
                                @foreach($origenes as $o)
                                    <option value="{{ $o->id }}">{{ $o->nombre }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Prioridad</label>
                            <x-ui.select
                                x-model="form.prioridad_id"
                                @change="delete errors.prioridad_id"
                                x-error="errors.prioridad_id">
                                <option value="">Sin prioridad</option>
                                @foreach($prioridades as $p)
                                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Valor estimado ($)</label>
                            <x-ui.input
                                type="number"
                                x-model="form.valor_estimado"
                                @input="delete errors.valor_estimado"
                                x-error="errors.valor_estimado"
                                placeholder="0" min="0" step="1000"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Probabilidad (%)</label>
                            <x-ui.input
                                type="number"
                                x-model="form.probabilidad_cierre"
                                @input="delete errors.probabilidad_cierre"
                                x-error="errors.probabilidad_cierre"
                                placeholder="Automática del estado" min="0" max="100"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Próximo contacto</label>
                            <x-ui.input
                                type="date"
                                x-model="form.fecha_proximo_contacto"
                                @change="delete errors.fecha_proximo_contacto"
                                x-error="errors.fecha_proximo_contacto"/>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Observaciones</label>
                        <textarea
                            x-model="form.observaciones"
                            rows="3"
                            placeholder="Notas adicionales..."
                            class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-ui.button href="{{ route('prospectos.index') }}" variant="ghost">Cancelar</x-ui.button>
                        <x-ui.button @click="guardar" variant="primary" x-bind:disabled="loading">
                            <span x-show="!loading">Crear prospecto</span>
                            <span x-show="loading" x-cloak>Guardando...</span>
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
