<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Existencias MP · SFconnecting</title>
    <link rel="icon" type="image/svg+xml" href="/images/logo.svg">
    <link rel="alternate icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
    let avisoEdicionTimeout;
    function mostrarAvisoEdicion() {
        const el = document.getElementById('aviso-edicion');
        if (!el) return;
        clearTimeout(avisoEdicionTimeout);
        el.classList.remove('translate-x-[120%]');
        el.classList.add('translate-x-0');
        avisoEdicionTimeout = setTimeout(() => {
            el.classList.remove('translate-x-0');
            el.classList.add('translate-x-[120%]');
        }, 5000);
    }

    // Rótulos de costales de salida de MP (SLI de SGP): buscar → pesos → confirmar → imprimir.
    function rotulosCostales() {
        const csrf = () => document.querySelector('meta[name="csrf-token"]').content;
        const pedir = async (url, opciones = {}) => {
            const res = await fetch(url, {
                ...opciones,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                },
            });
            let data = {};
            try { data = await res.json(); } catch (e) {}
            if (!res.ok || !data.success) {
                const errores = data.errors ? Object.values(data.errors).flat() : [];
                throw new Error(data.message && !errores.length ? data.message : (errores[0] || data.message || 'No se pudo completar la operación.'));
            }
            return data;
        };

        return {
            open: false,
            paso: 'buscar',
            documento: '',
            cargando: false,
            enviando: false,
            error: '',
            consulta: null,
            renglones: [],
            plan: null,

            abrir() {
                this.open = true;
                this.$nextTick(() => this.$refs.inputDocumento?.focus());
            },
            cerrar() {
                if (this.enviando) return;
                this.open = false;
            },
            reiniciar() {
                this.paso = 'buscar';
                this.documento = '';
                this.error = '';
                this.consulta = null;
                this.renglones = [];
                this.plan = null;
                this.$nextTick(() => this.$refs.inputDocumento?.focus());
            },
            numero(v) {
                const n = parseFloat(v);
                return isNaN(n) ? v : n.toLocaleString('es-CO', { maximumFractionDigits: 5 });
            },
            pesos() {
                const pesos = {};
                this.renglones.forEach(r => { pesos[r.id_row] = r.peso === '' || r.peso === null ? null : String(r.peso); });
                return pesos;
            },
            cambioPeso() {
                if (this.paso === 'confirmar') {
                    this.paso = 'pesos';
                    this.plan = null;
                }
                this.error = '';
            },

            async buscar() {
                if (!this.documento.trim() || this.cargando) return;
                this.cargando = true;
                this.error = '';
                try {
                    const url = '{{ route('rotulos-mp.consultar') }}?documento=' + encodeURIComponent(this.documento.trim());
                    const data = await pedir(url);
                    this.consulta = data;
                    this.documento = data.documento;
                    this.renglones = data.renglones.map(r => ({ ...r, peso: '' }));
                    this.paso = 'pesos';
                    this.$nextTick(() => this.$el.querySelector('[data-peso]')?.focus());
                } catch (e) {
                    this.error = e.message;
                } finally {
                    this.cargando = false;
                }
            },

            async calcular() {
                if (this.cargando) return;
                this.cargando = true;
                this.error = '';
                try {
                    this.plan = await pedir('{{ route('rotulos-mp.previsualizar') }}', {
                        method: 'POST',
                        body: JSON.stringify({ documento: this.consulta.documento, pesos: this.pesos() }),
                    });
                    this.paso = 'confirmar';
                } catch (e) {
                    this.error = e.message;
                } finally {
                    this.cargando = false;
                }
            },

            async imprimir() {
                if (this.enviando || this.paso !== 'confirmar') return;
                this.enviando = true;
                this.error = '';
                try {
                    const data = await pedir('{{ route('rotulos-mp.imprimir') }}', {
                        method: 'POST',
                        body: JSON.stringify({ documento: this.consulta.documento, pesos: this.pesos() }),
                    });
                    Alpine.store('toast').success(`Se enviaron ${data.total_rotulos} rótulos del ${data.documento} a ${data.impresora.nombre}.`);
                    this.enviando = false;
                    this.reiniciar();
                } catch (e) {
                    this.error = e.message;
                    this.enviando = false;
                }
            },
        };
    }

    function existenciasGrid(items) {
        return {
            items,
            editKey: null,
            editCampo: null,
            editValor: '',
            editValorOriginal: '',
            abrirEdicion(item, campo) {
                this.editKey = item._key;
                this.editCampo = campo;
                this.editValor = campo === 'fecha_vencimiento' ? item.FechaVencimiento : item.Ubicacion;
                this.editValorOriginal = this.editValor;
                mostrarAvisoEdicion();
            },
            cancelar() {
                this.editKey = null;
                this.editCampo = null;
            },
            fmtFecha(v) {
                const [y, m, d] = v.split('-');
                return `${d}/${m}/${y}`;
            },
            async guardar(item) {
                if (this.editKey !== item._key) return;
                const campo = this.editCampo;
                const valor = this.editValor;

                this.editKey = null;
                this.editCampo = null;

                if (valor === this.editValorOriginal) return;

                try {
                    const res = await fetch('{{ route('existencias-mp.actualizar') }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            compania:   item.Compania,
                            cod_bodega: item.CodBodega,
                            referencia: item.Referencia,
                            lote:       item.Lote,
                            campo,
                            valor,
                        }),
                    });
                    const data = await res.json();
                    if (!data.success) {
                        $store.toast.error(data.message || 'No se pudo guardar el cambio.');
                        return;
                    }
                    if (campo === 'fecha_vencimiento') {
                        item.FechaVencimiento = valor;
                    } else {
                        item.Ubicacion = valor;
                    }
                    $store.toast.success('Guardado.');
                } catch (e) {
                    $store.toast.error('Error de conexión al guardar.');
                }
            },
        };
    }
    </script>
</head>
<body class="h-full font-sans antialiased bg-slate-50">

<header class="sticky top-0 z-20 bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                <x-ui.icon name="file-text" class="w-5 h-5 text-blue-600"/>
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-900 leading-tight">Existencias de Materia Prima</h1>
                <p class="text-xs text-slate-400 mt-0.5">Consulta de lotes · vw_ExistenciasMP</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            {{-- Rótulos de costales (salida de MP a producción, SLI de SGP) --}}
            <div x-data="rotulosCostales()">
                <button type="button" @click="abrir()"
                        class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full font-semibold border border-blue-600 bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/>
                    </svg>
                    Rótulos Material
                </button>

                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="cerrar()">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="cerrar()"
                         x-show="open" x-transition.opacity></div>

                    <div role="dialog" aria-modal="true" aria-labelledby="rotulos-titulo" x-trap.noscroll="open"
                         class="relative w-full max-w-3xl max-h-[90vh] flex flex-col bg-white rounded-2xl shadow-2xl overflow-hidden"
                         x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">

                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                            <div>
                                <h3 id="rotulos-titulo" class="text-sm font-semibold text-slate-900">Rótulos de costales · Salida de MP</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Un rótulo por costal, con el peso de ese costal.</p>
                            </div>
                            <button type="button" @click="cerrar()" :disabled="enviando" aria-label="Cerrar"
                                    class="text-slate-400 hover:text-slate-600 disabled:opacity-40 transition-colors">
                                <x-ui.icon name="x" class="w-5 h-5"/>
                            </button>
                        </div>

                        <div class="px-6 py-5 overflow-y-auto space-y-5">
                            {{-- Paso 1: documento --}}
                            <form @submit.prevent="buscar()" class="flex items-end gap-3">
                                <label class="flex-1 max-w-xs">
                                    <span class="block text-xs font-semibold text-slate-600 mb-1">Documento de salida (SLI)</span>
                                    <input type="text" x-model="documento" x-ref="inputDocumento" :disabled="paso !== 'buscar'"
                                           placeholder="Ej: SLI299428" autocomplete="off"
                                           class="w-full px-3 py-2 text-sm font-mono uppercase border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:bg-slate-50 disabled:text-slate-500">
                                </label>
                                <template x-if="paso === 'buscar'">
                                    <x-ui.button type="submit" size="sm" x-bind:disabled="cargando || !documento.trim()">
                                        <span x-text="cargando ? 'Buscando…' : 'Buscar'"></span>
                                    </x-ui.button>
                                </template>
                                <template x-if="paso !== 'buscar'">
                                    <x-ui.button type="button" variant="ghost" size="sm" @click="reiniciar()" x-bind:disabled="enviando">
                                        Otro documento
                                    </x-ui.button>
                                </template>
                            </form>

                            {{-- Paso 2: datos del documento y peso por costal --}}
                            <template x-if="consulta">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                        <div class="sm:col-span-2">
                                            <p class="text-slate-400">Cliente</p>
                                            <p class="font-semibold text-slate-800" x-text="renglones[0]?.cliente || '—'"></p>
                                        </div>
                                        <div>
                                            <p class="text-slate-400">Pedido</p>
                                            <p class="font-mono text-slate-700 break-all" x-text="renglones[0]?.pedido || '—'"></p>
                                        </div>
                                        <div>
                                            <p class="text-slate-400">Fórmula</p>
                                            <p class="font-mono text-slate-700" x-text="renglones[0]?.formula || '—'"></p>
                                        </div>
                                    </div>

                                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                                        <table class="w-full text-sm">
                                            <thead class="bg-slate-50 border-b border-slate-200">
                                                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                                    <th class="px-3 py-2">Material</th>
                                                    <th class="px-3 py-2">Lote</th>
                                                    <th class="px-3 py-2 text-right">Cantidad</th>
                                                    <th class="px-3 py-2 w-48">Peso por costal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                <template x-for="r in renglones" :key="r.id_row">
                                                    <tr>
                                                        <td class="px-3 py-2">
                                                            <p class="font-medium text-slate-900" x-text="r.nombre_material || r.ref"></p>
                                                            <p class="text-xs text-slate-400">
                                                                <span class="font-mono" x-text="r.ref"></span>
                                                                <span x-show="r.capa" x-text="' · ' + r.capa"></span>
                                                            </p>
                                                        </td>
                                                        <td class="px-3 py-2 font-mono text-xs text-slate-500" x-text="r.lote || '—'"></td>
                                                        <td class="px-3 py-2 text-right font-semibold text-slate-800 whitespace-nowrap"
                                                            x-text="numero(r.cantidad) + ' ' + (r.unidad || '')"></td>
                                                        <td class="px-3 py-2">
                                                            <div class="flex items-center gap-1.5">
                                                                <input type="number" min="0" step="any" inputmode="decimal" data-peso
                                                                       x-model="r.peso" @input="cambioPeso()" :disabled="enviando"
                                                                       @keydown.enter.prevent="calcular()"
                                                                       class="w-full px-2 py-1.5 text-sm text-right border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                                                                <span class="text-xs font-semibold text-slate-500 w-10" x-text="r.unidad || ''"></span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="text-xs text-slate-400">
                                        Escribe el peso en la <strong class="text-slate-600">misma unidad</strong> del material (si es GRS, 25 kg = 25000).
                                        Deja vacío un material para no imprimirle rótulos.
                                    </p>
                                </div>
                            </template>

                            {{-- Paso 3: resumen antes de imprimir --}}
                            <template x-if="paso === 'confirmar' && plan">
                                <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 space-y-1.5">
                                    <p class="font-semibold">
                                        Se imprimirán <span x-text="plan.total_rotulos"></span> rótulos
                                        en <span x-text="consulta.impresora.nombre"></span>:
                                    </p>
                                    <template x-for="m in plan.materiales" :key="m.id_row">
                                        <p class="text-xs">
                                            <span class="font-mono" x-text="m.ref"></span> →
                                            <span x-text="m.resumen"></span>
                                            (<span x-text="m.num_rotulos"></span> rótulos)
                                        </p>
                                    </template>
                                </div>
                            </template>

                            <template x-if="error">
                                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" x-text="error"></div>
                            </template>
                        </div>

                        <div class="flex items-center justify-between gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/60" x-show="consulta">
                            <p class="text-xs text-slate-400">
                                Impresora: <span class="font-semibold text-slate-600" x-text="consulta?.impresora?.nombre"></span>
                                <span x-text="'(' + (consulta?.impresora?.ip || '') + ')'"></span>
                            </p>
                            <div class="flex items-center gap-2">
                                <template x-if="paso === 'pesos'">
                                    <x-ui.button type="button" size="sm" @click="calcular()" x-bind:disabled="cargando">
                                        <span x-text="cargando ? 'Calculando…' : 'Calcular rótulos'"></span>
                                    </x-ui.button>
                                </template>
                                <template x-if="paso === 'confirmar'">
                                    <x-ui.button type="button" size="sm" @click="imprimir()" x-bind:disabled="enviando">
                                        <span x-text="enviando ? 'Imprimiendo…' : 'Imprimir ' + plan.total_rotulos + ' rótulos'"></span>
                                    </x-ui.button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hidden sm:block w-px h-6 bg-slate-200"></div>
            <div class="hidden sm:flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center text-white text-xs font-semibold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <span class="text-sm text-slate-600 font-medium">{{ auth()->user()->name ?? '' }}</span>
            </div>
            <div class="hidden sm:block w-px h-6 bg-slate-200"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="ghost" size="sm">
                    <x-ui.icon name="log-out" class="w-3.5 h-3.5"/>
                    Cerrar sesión
                </x-ui.button>
            </form>
        </div>
    </div>
</header>

<main class="max-w-[1600px] mx-auto px-4 sm:px-6 py-6">

    @php
        $vencePronto = $diasVencer === 60;
        $baseQuery   = collect(request()->except(['dias_vencer', 'page']))->all();
        $urlVenceOn  = request()->url().'?'.http_build_query(array_merge($baseQuery, ['dias_vencer' => 60]));
        $urlVenceOff = request()->url().(count($baseQuery) ? '?'.http_build_query($baseQuery) : '');
    @endphp
    <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
        <form method="GET" class="flex-1 min-w-[240px] max-w-sm">
            @if($ordenarPor)
                <input type="hidden" name="sort" value="{{ $ordenarPor }}">
                <input type="hidden" name="dir" value="{{ $direccion }}">
            @endif
            @if($diasVencer !== null)
                <input type="hidden" name="dias_vencer" value="{{ $diasVencer }}">
            @endif
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Buscar por referencia, descripción o lote..."
                       class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
            </div>
        </form>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ $vencePronto ? $urlVenceOff : $urlVenceOn }}"
               class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full font-semibold border transition-colors {{ $vencePronto ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/>
                </svg>
                Vencen en 2 meses
                @if($vencePronto)
                    <span class="ml-0.5">×</span>
                @endif
            </a>

            @if($erpDisponible)
            <span class="inline-flex items-center gap-1.5 text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-semibold border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ number_format($total, 0, ',', '.') }} lotes
            </span>
            @endif
        </div>
    </div>

    @if(!$erpDisponible)
    <x-ui.card class="p-10 text-center">
        <p class="text-slate-500 text-sm">Sin conexión al ERP. No se pueden cargar las existencias en este momento.</p>
    </x-ui.card>

    @elseif(empty($existencias))
    <x-ui.card class="overflow-hidden">
        <x-ui.empty-state icon="search" title="Sin resultados" description="No se encontraron lotes con ese criterio de búsqueda."/>
    </x-ui.card>

    @else
    @php
        $itemsJs = array_map(function ($item) {
            return [
                '_key'              => ($item['CodBodega'] ?? '').'|'.($item['Referencia'] ?? '').'|'.($item['Lote'] ?? ''),
                'Compania'          => (int) ($item['Compania'] ?? 0),
                'CodBodega'         => $item['CodBodega'] ?? '',
                'Bodega'            => $item['Bodega'] ?? '—',
                'Referencia'        => $item['Referencia'] ?? '',
                'DescItem'          => $item['DescItem'] ?? '—',
                'Lote'              => $item['Lote'] ?? '',
                'UM'                => trim($item['UM'] ?? '') ?: '—',
                'Existencia'        => number_format((float) ($item['Existencia'] ?? 0), 2, ',', '.'),
                'ExistenciaRaw'     => (float) ($item['Existencia'] ?? 0),
                'UltEntrada'        => !empty($item['UltEntrada']) ? \Carbon\Carbon::parse($item['UltEntrada'])->format('d/m/Y') : '—',
                'UltEntradaRaw'     => !empty($item['UltEntrada']) ? \Carbon\Carbon::parse($item['UltEntrada'])->format('Y-m-d') : '',
                'FechaVencimiento'  => !empty($item['FechaVencimiento']) ? \Carbon\Carbon::parse($item['FechaVencimiento'])->format('Y-m-d') : '',
                'Ubicacion'         => $item['Ubicacion'] ?? '',
                'DiasParaVencer'    => $item['DiasParaVencer'] ?? '—',
                'EstadoVencimiento' => $item['EstadoVencimiento'] ?? null,
            ];
        }, $existencias);

        $sortLink = function (string $campo) use ($ordenarPor, $direccion) {
            $activo         = $ordenarPor === $campo;
            $nuevaDireccion = $activo && $direccion === 'asc' ? 'desc' : 'asc';

            return [
                'href'   => request()->fullUrlWithQuery(['sort' => $campo, 'dir' => $nuevaDireccion, 'page' => 1]),
                'activo' => $activo,
                'flecha' => $activo ? ($direccion === 'asc' ? '↑' : '↓') : '',
            ];
        };
    @endphp
    <x-ui.card class="overflow-hidden" :x-data="'existenciasGrid(' . \Illuminate\Support\Js::from($itemsJs) . ')'">
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead class="bg-gradient-to-b from-slate-50 to-slate-100/70 border-b-2 border-slate-200">
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        @foreach([
                            ['campo' => 'Bodega', 'label' => 'Bodega'],
                            ['campo' => 'Referencia', 'label' => 'Referencia'],
                            ['campo' => 'DescItem', 'label' => 'Descripción'],
                            ['campo' => 'Lote', 'label' => 'Lote'],
                            ['campo' => 'UM', 'label' => 'UM'],
                            ['campo' => 'Existencia', 'label' => 'Existencia', 'align' => 'right'],
                            ['campo' => 'UltEntrada', 'label' => 'Últ. Entrada'],
                            ['campo' => 'FechaVencimiento', 'label' => 'Fecha Vencimiento'],
                            ['campo' => 'Ubicacion', 'label' => 'Ubicación'],
                            ['campo' => 'DiasParaVencer', 'label' => 'Días p/Vencer', 'align' => 'right'],
                            ['campo' => 'EstadoVencimiento', 'label' => 'Estado'],
                        ] as $col)
                            @php $s = $sortLink($col['campo']); @endphp
                            <th class="px-4 py-3 {{ ($col['align'] ?? '') === 'right' ? 'text-right' : '' }} hover:bg-slate-200/50 transition-colors">
                                <a href="{{ $s['href'] }}" class="inline-flex items-center gap-1 hover:text-slate-800 transition-colors {{ $s['activo'] ? 'text-blue-600' : '' }} {{ ($col['align'] ?? '') === 'right' ? 'justify-end w-full' : '' }}">
                                    {{ $col['label'] }} <span class="text-blue-500 w-2.5 inline-block">{{ $s['flecha'] }}</span>
                                </a>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="item in items" :key="item._key">
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="text-slate-900" x-text="item.Bodega"></p>
                            <p class="text-xs text-slate-400" x-text="item.CodBodega"></p>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500" x-text="item.Referencia"></td>
                        <td class="px-4 py-3 font-medium text-slate-900 whitespace-normal min-w-[220px]" x-text="item.DescItem"></td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500" x-text="item.Lote"></td>
                        <td class="px-4 py-3 text-slate-500" x-text="item.UM"></td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-800" x-text="item.Existencia"></td>
                        <td class="px-4 py-3 text-xs text-slate-500" x-text="item.UltEntrada"></td>

                        {{-- Fecha Vencimiento — click para editar --}}
                        <td class="px-4 py-3 text-xs text-slate-500">
                            <template x-if="editKey !== item._key || editCampo !== 'fecha_vencimiento'">
                                <div @click="abrirEdicion(item, 'fecha_vencimiento')"
                                     class="cursor-pointer hover:bg-blue-50 rounded px-1.5 py-1 -mx-1.5 flex items-center gap-1 group">
                                    <span x-text="item.FechaVencimiento ? fmtFecha(item.FechaVencimiento) : '—'"></span>
                                    <svg class="w-3 h-3 text-slate-300 opacity-0 group-hover:opacity-100 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </div>
                            </template>
                            <template x-if="editKey === item._key && editCampo === 'fecha_vencimiento'">
                                <input type="date" x-model="editValor"
                                       @keydown.enter="guardar(item)"
                                       @keydown.escape="cancelar()"
                                       @blur="guardar(item)"
                                       x-init="$nextTick(() => $el.focus())"
                                       class="w-32 text-xs border border-blue-300 rounded px-1.5 py-1 focus:outline-none focus:ring-1 focus:ring-blue-400">
                            </template>
                        </td>

                        {{-- Ubicación — click para editar --}}
                        <td class="px-4 py-3 text-xs text-slate-500">
                            <template x-if="editKey !== item._key || editCampo !== 'ubicacion'">
                                <div @click="abrirEdicion(item, 'ubicacion')"
                                     class="cursor-pointer hover:bg-blue-50 rounded px-1.5 py-1 -mx-1.5 flex items-center gap-1 group">
                                    <span x-text="item.Ubicacion || '—'"></span>
                                    <svg class="w-3 h-3 text-slate-300 opacity-0 group-hover:opacity-100 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </div>
                            </template>
                            <template x-if="editKey === item._key && editCampo === 'ubicacion'">
                                <input type="text" x-model="editValor"
                                       placeholder="Ej: Estante A-3"
                                       @keydown.enter="guardar(item)"
                                       @keydown.escape="cancelar()"
                                       @blur="guardar(item)"
                                       x-init="$nextTick(() => $el.focus())"
                                       class="w-32 text-xs border border-blue-300 rounded px-1.5 py-1 focus:outline-none focus:ring-1 focus:ring-blue-400">
                            </template>
                        </td>

                        <td class="px-4 py-3 text-right text-xs font-semibold"
                            :class="{
                                'text-red-600': item.DiasParaVencer !== '—' && item.DiasParaVencer < 0,
                                'text-amber-600': item.DiasParaVencer !== '—' && item.DiasParaVencer >= 0 && item.DiasParaVencer <= 60,
                                'text-slate-500': item.DiasParaVencer === '—' || item.DiasParaVencer > 60,
                            }"
                            x-text="item.DiasParaVencer"></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                  :class="{
                                      'bg-red-100 text-red-700': item.EstadoVencimiento === 'VENCIDO',
                                      'bg-amber-100 text-amber-700': item.EstadoVencimiento === 'POR VENCER (<=30d)',
                                      'bg-orange-100 text-orange-700': item.EstadoVencimiento === 'PROXIMO (<=90d)',
                                      'bg-emerald-100 text-emerald-700': item.EstadoVencimiento === 'VIGENTE',
                                      'bg-slate-100 text-slate-500': !item.EstadoVencimiento || item.EstadoVencimiento === 'SIN FECHA',
                                  }"
                                  x-text="item.EstadoVencimiento || '—'">
                            </span>
                        </td>
                    </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </x-ui.card>

    {{-- Paginación --}}
    @if($totalPaginas > 1)
    <div class="mt-4 flex items-center justify-between">
        <p class="text-xs text-slate-400">
            Página {{ $pagina }} de {{ $totalPaginas }} · {{ number_format($total, 0, ',', '.') }} lotes en total
        </p>
        <div class="flex items-center gap-1">
            @if($pagina > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => $pagina - 1]) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                    Anterior
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-100 text-slate-300 cursor-not-allowed">
                    Anterior
                </span>
            @endif

            @php
                $desde = max(1, $pagina - 2);
                $hasta = min($totalPaginas, $pagina + 2);
            @endphp
            @if($desde > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}"
                   class="inline-flex items-center justify-center w-8 h-8 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">1</a>
                @if($desde > 2)<span class="px-1 text-slate-300 text-xs">…</span>@endif
            @endif
            @for($i = $desde; $i <= $hasta; $i++)
                @if($i === $pagina)
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs rounded-lg bg-blue-600 text-white font-semibold">{{ $i }}</span>
                @else
                    <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                       class="inline-flex items-center justify-center w-8 h-8 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">{{ $i }}</a>
                @endif
            @endfor
            @if($hasta < $totalPaginas)
                @if($hasta < $totalPaginas - 1)<span class="px-1 text-slate-300 text-xs">…</span>@endif
                <a href="{{ request()->fullUrlWithQuery(['page' => $totalPaginas]) }}"
                   class="inline-flex items-center justify-center w-8 h-8 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">{{ $totalPaginas }}</a>
            @endif

            @if($pagina < $totalPaginas)
                <a href="{{ request()->fullUrlWithQuery(['page' => $pagina + 1]) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                    Siguiente
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-100 text-slate-300 cursor-not-allowed">
                    Siguiente
                </span>
            @endif
        </div>
    </div>
    @endif
    @endif

</main>

<div id="aviso-edicion" class="fixed top-4 right-4 z-50 w-80 translate-x-[120%] transition-transform duration-300 ease-out">
    <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 shadow-lg text-sm text-amber-800">
        <svg class="w-4.5 h-4.5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
        <span class="flex-1">Verifica bien la información antes de guardar.</span>
    </div>
</div>

<div x-data class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 w-80" aria-live="polite">
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div x-show="true"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             :class="{
                 'bg-emerald-50 border-emerald-200 text-emerald-800': toast.type === 'success',
                 'bg-red-50 border-red-200 text-red-800': toast.type === 'error',
                 'bg-amber-50 border-amber-200 text-amber-800': toast.type === 'warning',
             }"
             class="flex items-start gap-3 rounded-xl border px-4 py-3 shadow-lg text-sm">
            <span class="flex-1" x-text="toast.message"></span>
            <button @click="$store.toast.remove(toast.id)" class="opacity-60 hover:opacity-100 text-lg leading-none">×</button>
        </div>
    </template>
</div>

</body>
</html>
