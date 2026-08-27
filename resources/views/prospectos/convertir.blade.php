<x-layouts.app :title="'Convertir en cliente: ' . $prospecto->empresa">
    <x-slot name="actions">
        <x-ui.button href="{{ route('prospectos.show', $prospecto) }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6"
        x-data="{
            loading: false,
            archivoNombre: null,
            cargandoArchivo: false,
            dragOver: false,
            campos: {{ \Illuminate\Support\Js::from(config('cliente_datos_carga_campos')) }},
            fila: null,
            error: null,

            solicitaCupo: false,
            cupoMensualSolicitado: '',
            condicionesPagoDias: '',
            inventarioConsignacion: '',
            referencia1: { empresa: '', telefono: '', nit: '' },
            referencia2: { empresa: '', telefono: '', nit: '' },

            normalizar(texto) {
                const acentos = { 'á': 'a', 'é': 'e', 'í': 'i', 'ó': 'o', 'ú': 'u', 'ü': 'u', 'ñ': 'n' };
                return (texto || '')
                    .toString()
                    .toLowerCase()
                    .replace(/[áéíóúüñ]/g, c => acentos[c] || c)
                    .replace(/[_-]/g, ' ')
                    .replace(/[^a-z0-9 ]/g, '')
                    .replace(/\s+/g, ' ')
                    .trim();
            },

            // Orden de prioridad: se usa el primer delimitador cuyo conteo de columnas
            // sea >1 y consistente en las primeras líneas del archivo.
            delimitadoresCandidatos: [',', ';', '\t', '|', '-', /\s+/],
            dividirLinea(linea, delimitador) {
                return delimitador instanceof RegExp
                    ? linea.trim().split(delimitador)
                    : linea.split(delimitador);
            },
            detectarDelimitador(lineas) {
                const muestra = lineas.slice(0, Math.min(5, lineas.length));
                for (const delimitador of this.delimitadoresCandidatos) {
                    const conteos = muestra.map(l => this.dividirLinea(l, delimitador).length);
                    const cols = conteos[0];
                    const consistente = cols > 1 && conteos.every(c => c === cols);
                    if (consistente) return delimitador;
                }
                return ',';
            },

            // Empareja los encabezados del archivo con los campos conocidos
            // (config/cliente_datos_carga_campos.php), sin importar tildes/mayúsculas/guiones.
            mapearFila(encabezados, valores) {
                const encabezadosNorm = encabezados.map(h => this.normalizar(h));
                const fila = {};
                this.campos.forEach(campo => {
                    const idx = encabezadosNorm.findIndex(h => campo.sinonimos.some(s => this.normalizar(s) === h));
                    fila[campo.key] = idx !== -1 ? (valores[idx] ?? '').trim() : '';
                });
                return fila;
            },

            procesarArchivo(file) {
                if (!file) return;
                this.error = null;
                this.cargandoArchivo = true;
                this.fila = null;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const lineas = e.target.result.split(/\r\n|\n|\r/).filter(l => l.trim() !== '');
                    setTimeout(() => {
                        this.cargandoArchivo = false;
                        if (lineas.length < 1) { this.error = 'El archivo está vacío.'; return; }
                        if (lineas.length > 2) {
                            this.error = 'El archivo trae más de un cliente. Este cargue es para un solo cliente a la vez — separa cada uno en su propio archivo.';
                            return;
                        }
                        const delimitador = this.detectarDelimitador(lineas);
                        const encabezados = this.dividirLinea(lineas[0], delimitador).map(c => c.trim());
                        const valores = this.dividirLinea(lineas[1], delimitador);
                        this.archivoNombre = file.name;
                        this.fila = this.mapearFila(encabezados, valores);
                    }, 500);
                };
                reader.readAsText(file);
            },

            referenciasParaEnviar() {
                const refs = [this.referencia1, this.referencia2].filter(r => r.empresa || r.telefono || r.nit);
                return refs.length ? refs : null;
            },

            guardar() {
                this.loading = true;
                $api('POST', '/api/prospectos/{{ $prospecto->id }}/convertir', {
                    razon_social: this.fila.razon_social || '{{ $prospecto->empresa }}',
                    nit: this.fila.nit || null,
                    ciudad: this.fila.ciudad_correspondencia || null,
                    direccion: this.fila.direccion_correspondencia || null,
                    datos_carga: [this.fila],
                    solicita_cupo: this.solicitaCupo,
                    monto_solicitado: this.solicitaCupo ? this.cupoMensualSolicitado : null,
                    plazo_solicitado_dias: this.solicitaCupo ? (this.condicionesPagoDias || null) : null,
                    referencias_comerciales: this.solicitaCupo ? this.referenciasParaEnviar() : null,
                    inventario_consignacion: this.solicitaCupo ? (this.inventarioConsignacion === '' ? null : this.inventarioConsignacion === 'si') : null,
                }).then(r => {
                    if (r.success) {
                        $store.toast.success(r.message);
                        setTimeout(() => window.location.href = '{{ route('prospectos.show', $prospecto) }}', 800);
                    } else {
                        $store.toast.error(r.message ?? 'Error al convertir');
                        this.loading = false;
                    }
                }).catch(() => { $store.toast.error('Error de conexión'); this.loading = false; });
            },
        }"
    >
        <x-ui.card>
            <div class="p-6 space-y-5">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 mb-1">Archivo plano (CSV / TXT)</h2>
                    <p class="text-sm text-slate-500">Arrastra el archivo con la información del cliente "{{ $prospecto->empresa }}" o haz clic para buscarlo. Debe traer un encabezado y una sola fila de datos.</p>
                </div>

                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <div class="flex items-start gap-2.5">
                        <x-ui.icon name="info" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5"/>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-blue-900 mb-2">Estos son los 19 campos que debe traer el archivo</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-1.5">
                                @foreach(config('cliente_datos_carga_campos') as $campo)
                                <div class="flex items-start gap-1.5 text-sm text-blue-900">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 shrink-0 mt-1.5"></span>
                                    <span>{{ $campo['label'] }}</span>
                                </div>
                                @endforeach
                            </div>
                            <p class="text-xs text-blue-700 mt-3">Si el archivo no trae alguno de estos campos, se puede completar manualmente después de cargarlo.</p>
                        </div>
                    </div>
                </div>

                <label
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="dragOver = false; procesarArchivo($event.dataTransfer.files[0])"
                    :class="dragOver ? 'border-blue-400 bg-blue-50' : 'border-slate-300 hover:bg-slate-50'"
                    class="flex flex-col items-center justify-center gap-2 text-center border-2 border-dashed rounded-lg px-6 py-10 cursor-pointer transition-colors"
                >
                    <template x-if="!cargandoArchivo">
                        <div class="flex flex-col items-center gap-1.5">
                            <x-ui.icon name="file-text" class="w-8 h-8 text-slate-400"/>
                            <p class="text-sm font-medium text-slate-700" x-text="archivoNombre ? 'Archivo cargado: ' + archivoNombre : 'Arrastra el archivo aquí o haz clic para buscarlo'"></p>
                            <p class="text-xs text-slate-400">Formatos: .csv, .txt — separador detectado automáticamente</p>
                        </div>
                    </template>
                    <template x-if="cargandoArchivo">
                        <div class="flex items-center gap-2 text-slate-500 text-sm font-medium">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            Cargando información del archivo...
                        </div>
                    </template>
                    <input type="file" accept=".csv,.txt" class="hidden" @change="procesarArchivo($event.target.files[0])">
                </label>

                <p x-show="error" x-text="error" class="text-sm text-red-600"></p>

                <div x-show="fila" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4 border border-slate-200 rounded-lg p-4">
                    <template x-for="campo in campos" :key="campo.key">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1" x-text="campo.label"></label>
                            <input
                                :type="campo.tipo === 'date' ? 'date' : (campo.tipo === 'email' ? 'email' : 'text')"
                                x-model="fila[campo.key]"
                                :placeholder="fila[campo.key] ? '' : 'Rellenar...'"
                                :class="fila[campo.key] ? 'border-slate-200' : 'border-amber-300 bg-amber-50 placeholder-amber-500'"
                                class="w-full px-2 py-1.5 text-sm rounded border focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                    </template>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card x-show="fila" x-cloak>
            <div class="p-6 space-y-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" x-model="solicitaCupo" class="mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span>
                        <span class="block text-sm font-semibold text-slate-900">¿Solicitar cupo de crédito para este cliente?</span>
                        <span class="block text-sm text-slate-500 mt-0.5">Es una consulta de cuánto cupo están dispuestos a otorgarle a este cliente nuevo — no es un crédito ya adquirido. Con esa respuesta se define cómo se plantea la negociación.</span>
                    </span>
                </label>

                <div x-show="solicitaCupo" x-cloak class="space-y-4 border-t border-slate-100 pt-4">

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border border-slate-200 rounded-lg p-3">
                        <p class="sm:col-span-3 text-xs font-semibold text-slate-600 -mb-1">Referencia comercial 1</p>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Nombre empresa</label>
                            <input type="text" x-model="referencia1.empresa"
                                   class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Teléfono</label>
                            <input type="text" x-model="referencia1.telefono"
                                   class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">NIT</label>
                            <input type="text" x-model="referencia1.nit"
                                   class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border border-slate-200 rounded-lg p-3">
                        <p class="sm:col-span-3 text-xs font-semibold text-slate-600 -mb-1">Referencia comercial 2</p>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Nombre empresa</label>
                            <input type="text" x-model="referencia2.empresa"
                                   class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Teléfono</label>
                            <input type="text" x-model="referencia2.telefono"
                                   class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">NIT</label>
                            <input type="text" x-model="referencia2.nit"
                                   class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Condiciones de pago (días) <span class="text-red-500">*</span></label>
                            <input type="number" min="1" x-model="condicionesPagoDias"
                                   class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Cupo mensual de crédito solicitado ($) <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="numeric" placeholder="0"
                                   :value="cupoMensualSolicitado ? Number(cupoMensualSolicitado).toLocaleString('es-CO') : ''"
                                   @input="cupoMensualSolicitado = $event.target.value.replace(/\D/g, '')"
                                   class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Inventario en consignación</label>
                            <select x-model="inventarioConsignacion"
                                    class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecciona...</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <div class="flex items-center justify-end gap-3">
            <p x-show="!fila" x-cloak class="text-xs text-amber-600 mr-auto">Carga un archivo antes de confirmar.</p>
            <x-ui.button href="{{ route('prospectos.show', $prospecto) }}" variant="ghost">Cancelar</x-ui.button>
            <x-ui.button type="button" variant="primary" @click="guardar()" x-bind:disabled="loading || !fila || (solicitaCupo && (!cupoMensualSolicitado || !condicionesPagoDias))">
                <span x-show="!loading">Confirmar conversión</span>
                <span x-show="loading">Guardando...</span>
            </x-ui.button>
        </div>
    </div>
</x-layouts.app>
