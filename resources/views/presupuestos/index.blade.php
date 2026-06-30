<x-layouts.app title="Presupuestos · CRM">
<x-slot name="actions">
    <a href="{{ route('presupuestos.index', ['anio' => $anio]) }}"
       class="text-xs text-slate-400">Año {{ $anio }}</a>
</x-slot>

<div x-data="{ modalCrear: false, modalEditar: false, modalMeses: false, editando: null, editandoMeses: null }">

    {{-- ===== Cabecera ===== --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Presupuestos anuales</h2>
            <p class="text-xs text-slate-400 mt-0.5">Metas de venta por asesor</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('presupuestos.index') }}">
                <select name="anio" onchange="this.form.submit()"
                        class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($anios as $a)
                        <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </form>
            <button @click="modalCrear = true"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                <x-ui.icon name="plus" class="w-4 h-4"/>
                Nuevo presupuesto
            </button>
        </div>
    </div>

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

    {{-- ===== Tabla agrupada por asesor ===== --}}
    <x-ui.card class="overflow-hidden">
        @if($porAsesor->isEmpty())
            <x-ui.empty-state icon="bar-chart"
                title="Sin presupuestos para {{ $anio }}"
                description="Crea el primer presupuesto del año haciendo clic en «Nuevo presupuesto»." />
        @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Asesor</th>
                    <th class="px-5 py-3 text-right">
                        <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">Formacol</span>
                    </th>
                    <th class="px-5 py-3 text-right">
                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-semibold">Contiflex</span>
                    </th>
                    <th class="px-5 py-3 text-right">Total</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($porAsesor as $row)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold shrink-0">
                                {{ strtoupper(substr($row['asesor']?->name ?? 'U', 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">{{ $row['asesor']?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $row['asesor']?->email ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-right">
                        @if($row['cia1'])
                            <span class="font-semibold text-slate-900">${{ number_format($row['cia1']->presupuesto, 0, ',', '.') }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        @if($row['cia2'])
                            <span class="font-semibold text-slate-900">${{ number_format($row['cia2']->presupuesto, 0, ',', '.') }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right font-bold text-slate-700">
                        ${{ number_format(($row['cia1']?->presupuesto ?? 0) + ($row['cia2']?->presupuesto ?? 0), 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            {{-- Distribuir meses --}}
                            <button @click="editandoMeses = {{ json_encode([
                                'asesor_id'   => $row['asesor']?->id,
                                'asesor_name' => $row['asesor']?->name,
                                'anio'        => $row['anio'],
                                'cia1'        => $row['cia1'] ? [
                                    'id'    => $row['cia1']->id,
                                    'anual' => (float) $row['cia1']->presupuesto,
                                    'meses' => $row['cia1']->meses->pluck('valor', 'mes'),
                                ] : null,
                                'cia2'        => $row['cia2'] ? [
                                    'id'    => $row['cia2']->id,
                                    'anual' => (float) $row['cia2']->presupuesto,
                                    'meses' => $row['cia2']->meses->pluck('valor', 'mes'),
                                ] : null,
                            ]) }}; modalMeses = true"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                                    title="Distribuir por meses">
                                <x-ui.icon name="calendar" class="w-4 h-4"/>
                            </button>
                            {{-- Editar ambos --}}
                            <button @click="editando = {{ json_encode([
                                'asesor_id'   => $row['asesor']?->id,
                                'asesor_name' => $row['asesor']?->name,
                                'anio'        => $row['anio'],
                                'id'          => $row['cia1']?->id ?? $row['cia2']?->id,
                                'cia1_val'    => $row['cia1']?->presupuesto,
                                'cia2_val'    => $row['cia2']?->presupuesto,
                            ]) }}; modalEditar = true"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                    title="Editar">
                                <x-ui.icon name="edit" class="w-4 h-4"/>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 border-t border-slate-200">
                <tr>
                    <td class="px-5 py-3 text-sm font-semibold text-slate-700">
                        Total — {{ $porAsesor->count() }} asesores
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-bold text-red-700">
                        ${{ number_format($porAsesor->sum(fn($r) => $r['cia1']?->presupuesto ?? 0), 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-bold text-blue-700">
                        ${{ number_format($porAsesor->sum(fn($r) => $r['cia2']?->presupuesto ?? 0), 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-bold text-slate-900">
                        ${{ number_format($porAsesor->sum(fn($r) => ($r['cia1']?->presupuesto ?? 0) + ($r['cia2']?->presupuesto ?? 0)), 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        @endif
    </x-ui.card>

    {{-- ===== Modal Crear ===== --}}
    <div x-show="modalCrear" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="modalCrear = false">
        <div @click.outside="modalCrear = false"
             class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6"
             x-data="{ cia: '' }">
            <h3 class="text-base font-bold text-slate-900 mb-5">Nuevo presupuesto</h3>
            <form method="POST" action="{{ route('presupuestos.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Asesor</label>
                    <select name="asesor_id" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecciona un asesor...</option>
                        @foreach($asesores as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Año</label>
                    <select name="anio" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($anios as $a)
                            <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Compañía</label>
                    <select name="compania" required x-model="cia"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecciona...</option>
                        <option value="1">Formacol</option>
                        <option value="2">Contiflex</option>
                        <option value="0">Ambas</option>
                    </select>
                </div>

                <div x-show="cia === '1' || cia === '2'" x-data="pesoInput()" @reset.window="reset()">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5"
                           x-text="cia === '1' ? 'Presupuesto Formacol' : 'Presupuesto Contiflex'"></label>
                    <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500">
                        <span class="px-3 text-slate-400 text-sm shrink-0 border-r border-slate-200 bg-slate-50 py-2 select-none pointer-events-none">$</span>
                        <input type="text" inputmode="numeric"
                               :required="cia === '1' || cia === '2'"
                               x-model="display" @input="onInput($event)" @keydown="onKeydown($event)"
                               placeholder="0"
                               class="flex-1 px-3 py-2 text-sm focus:outline-none bg-transparent"/>
                    </div>
                    <input type="hidden" name="presupuesto" :value="raw"/>
                    <p class="text-xs text-slate-400 mt-1"
                       x-text="raw ? '= $' + Number(raw).toLocaleString('es-CO') + ' COP' : ''"></p>
                </div>

                <div x-show="cia === '0'" class="space-y-3">
                    <div x-data="pesoInput()">
                        <label class="block text-xs font-semibold text-red-600 mb-1.5">Presupuesto Formacol</label>
                        <div class="flex items-center border border-red-200 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-red-400">
                            <span class="px-3 text-slate-400 text-sm shrink-0 border-r border-red-200 bg-red-50 py-2 select-none pointer-events-none">$</span>
                            <input type="text" inputmode="numeric"
                                   :required="cia === '0'"
                                   x-model="display" @input="onInput($event)" @keydown="onKeydown($event)"
                                   placeholder="0"
                                   class="flex-1 px-3 py-2 text-sm focus:outline-none bg-transparent"/>
                        </div>
                        <input type="hidden" name="presupuesto_1" :value="raw"/>
                        <p class="text-xs text-slate-400 mt-1"
                           x-text="raw ? '= $' + Number(raw).toLocaleString('es-CO') + ' COP' : ''"></p>
                    </div>
                    <div x-data="pesoInput()">
                        <label class="block text-xs font-semibold text-blue-600 mb-1.5">Presupuesto Contiflex</label>
                        <div class="flex items-center border border-blue-200 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-400">
                            <span class="px-3 text-slate-400 text-sm shrink-0 border-r border-blue-200 bg-blue-50 py-2 select-none pointer-events-none">$</span>
                            <input type="text" inputmode="numeric"
                                   :required="cia === '0'"
                                   x-model="display" @input="onInput($event)" @keydown="onKeydown($event)"
                                   placeholder="0"
                                   class="flex-1 px-3 py-2 text-sm focus:outline-none bg-transparent"/>
                        </div>
                        <input type="hidden" name="presupuesto_2" :value="raw"/>
                        <p class="text-xs text-slate-400 mt-1"
                           x-text="raw ? '= $' + Number(raw).toLocaleString('es-CO') + ' COP' : ''"></p>
                    </div>
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
    </div>

    {{-- ===== Modal Editar ===== --}}
    <div x-show="modalEditar" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="modalEditar = false; editando = null">
        <div @click.outside="modalEditar = false; editando = null"
             class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-base font-bold text-slate-900 mb-1">Editar presupuesto</h3>
            <p class="text-sm text-slate-400 mb-5" x-text="editando?.asesor_name ?? ''"></p>
            <template x-if="editando">
                <form :action="`/presupuestos/${editando.id}`" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="asesor_id" :value="editando.asesor_id"/>
                    <input type="hidden" name="anio" :value="editando.anio"/>

                    <div x-data="pesoInput(editando.cia1_val)">
                        <label class="block text-xs font-semibold text-red-600 mb-1.5">
                            Presupuesto Formacol <span x-text="editando.anio"></span>
                        </label>
                        <div class="flex items-center border border-red-200 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-red-400">
                            <span class="px-3 text-slate-400 text-sm shrink-0 border-r border-red-200 bg-red-50 py-2">$</span>
                            <input type="text" inputmode="numeric"
                                   x-model="display" @input="onInput($event)" @keydown="onKeydown($event)"
                                   placeholder="Dejar vacío para no cambiar"
                                   class="flex-1 px-3 py-2 text-sm focus:outline-none bg-transparent"/>
                        </div>
                        <input type="hidden" name="presupuesto_1" :value="raw"/>
                        <p class="text-xs text-slate-400 mt-1"
                           x-text="raw ? '= $' + Number(raw).toLocaleString('es-CO') + ' COP' : (editando.cia1_val ? 'Actual: $' + Number(editando.cia1_val).toLocaleString('es-CO') : 'Sin presupuesto — se creará al guardar')"></p>
                    </div>

                    <div x-data="pesoInput(editando.cia2_val)">
                        <label class="block text-xs font-semibold text-blue-600 mb-1.5">
                            Presupuesto Contiflex <span x-text="editando.anio"></span>
                        </label>
                        <div class="flex items-center border border-blue-200 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-400">
                            <span class="px-3 text-slate-400 text-sm shrink-0 border-r border-blue-200 bg-blue-50 py-2">$</span>
                            <input type="text" inputmode="numeric"
                                   x-model="display" @input="onInput($event)" @keydown="onKeydown($event)"
                                   placeholder="Dejar vacío para no cambiar"
                                   class="flex-1 px-3 py-2 text-sm focus:outline-none bg-transparent"/>
                        </div>
                        <input type="hidden" name="presupuesto_2" :value="raw"/>
                        <p class="text-xs text-slate-400 mt-1"
                           x-text="raw ? '= $' + Number(raw).toLocaleString('es-CO') + ' COP' : (editando.cia2_val ? 'Actual: $' + Number(editando.cia2_val).toLocaleString('es-CO') : 'Sin presupuesto — se creará al guardar')"></p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="modalEditar = false; editando = null"
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

    {{-- ===== Modal Distribución Mensual ===== --}}
    <div x-show="modalMeses" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="modalMeses = false; editandoMeses = null">
        <div @click.outside="modalMeses = false; editandoMeses = null"
             class="bg-white rounded-2xl shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] flex flex-col">
            <template x-if="editandoMeses">
                <div x-data="modalMesesComp(editandoMeses)" class="flex flex-col min-h-0">

                    {{-- Header --}}
                    <div class="px-6 pt-6 pb-4 border-b border-slate-100 shrink-0">
                        <h3 class="text-base font-bold text-slate-900">Distribución mensual</h3>
                        <p class="text-sm text-slate-400 mt-0.5" x-text="asesor_name + ' · ' + anio"></p>
                    </div>

                    {{-- Body (scrollable) --}}
                    <form action="{{ route('presupuestos.meses.store') }}" method="POST" class="flex-1 overflow-y-auto px-6 py-5 space-y-6">
                        @csrf
                        <input type="hidden" name="asesor_id" :value="asesor_id"/>
                        <input type="hidden" name="anio" :value="anio"/>

                        {{-- Sección Formacol --}}
                        <template x-if="cia1">
                            <div>
                                <input type="hidden" name="cia1_id" :value="cia1.id"/>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-bold text-red-700 uppercase tracking-wider px-2 py-0.5 bg-red-50 rounded-full">Formacol</span>
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click="distribuirIgual('cia1')"
                                                class="text-xs text-slate-500 hover:text-red-600 underline underline-offset-2">
                                            Distribuir igualmente
                                        </button>
                                        <span class="text-xs font-semibold"
                                              :class="ok1 ? 'text-emerald-600' : 'text-red-500'">
                                            $<span x-text="fmt(suma1)"></span>
                                            /
                                            $<span x-text="fmt(cia1.anual)"></span>
                                        </span>
                                        <span x-show="!ok1" class="text-xs text-red-500 font-medium"
                                              x-text="diferencia1 > 0 ? '+$' + fmt(Math.abs(diferencia1)) : '-$' + fmt(Math.abs(diferencia1))">
                                        </span>
                                        <span x-show="ok1" class="text-xs text-emerald-600">✓ Cuadra</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                    <template x-for="m in 12" :key="'c1-' + m">
                                        <div>
                                            <label class="block text-center text-xs text-slate-400 mb-1"
                                                   x-text="mesesNombres[m - 1]"></label>
                                            <div class="border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-red-400"
                                                 :class="ok1 ? 'border-slate-200' : 'border-red-200'">
                                                <input type="text" inputmode="numeric"
                                                       :value="cia1.displays[m]"
                                                       @input="onInput('cia1', m, $event)"
                                                       @keydown="onKeydown($event)"
                                                       placeholder="0"
                                                       class="w-full px-2 py-1.5 text-xs text-right focus:outline-none bg-transparent"/>
                                            </div>
                                            <input type="hidden" :name="`cia1_mes[${m}]`" :value="cia1.vals[m]"/>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Sección Contiflex --}}
                        <template x-if="cia2">
                            <div>
                                <input type="hidden" name="cia2_id" :value="cia2.id"/>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-bold text-blue-700 uppercase tracking-wider px-2 py-0.5 bg-blue-50 rounded-full">Contiflex</span>
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click="distribuirIgual('cia2')"
                                                class="text-xs text-slate-500 hover:text-blue-600 underline underline-offset-2">
                                            Distribuir igualmente
                                        </button>
                                        <span class="text-xs font-semibold"
                                              :class="ok2 ? 'text-emerald-600' : 'text-red-500'">
                                            $<span x-text="fmt(suma2)"></span>
                                            /
                                            $<span x-text="fmt(cia2.anual)"></span>
                                        </span>
                                        <span x-show="!ok2" class="text-xs text-red-500 font-medium"
                                              x-text="diferencia2 > 0 ? '+$' + fmt(Math.abs(diferencia2)) : '-$' + fmt(Math.abs(diferencia2))">
                                        </span>
                                        <span x-show="ok2" class="text-xs text-emerald-600">✓ Cuadra</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                    <template x-for="m in 12" :key="'c2-' + m">
                                        <div>
                                            <label class="block text-center text-xs text-slate-400 mb-1"
                                                   x-text="mesesNombres[m - 1]"></label>
                                            <div class="border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-400"
                                                 :class="ok2 ? 'border-slate-200' : 'border-blue-200'">
                                                <input type="text" inputmode="numeric"
                                                       :value="cia2.displays[m]"
                                                       @input="onInput('cia2', m, $event)"
                                                       @keydown="onKeydown($event)"
                                                       placeholder="0"
                                                       class="w-full px-2 py-1.5 text-xs text-right focus:outline-none bg-transparent"/>
                                            </div>
                                            <input type="hidden" :name="`cia2_mes[${m}]`" :value="cia2.vals[m]"/>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Footer con botones dentro del form --}}
                        <div class="flex gap-3 pt-2 border-t border-slate-100">
                            <button type="button"
                                    @click="modalMeses = false; editandoMeses = null"
                                    class="flex-1 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    :disabled="!todoOk"
                                    :class="todoOk
                                        ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer'
                                        : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                                    class="flex-1 py-2 rounded-lg text-sm font-semibold transition-colors">
                                <span x-show="todoOk">Guardar distribución</span>
                                <span x-show="!todoOk">Los montos no cuadran</span>
                            </button>
                        </div>
                    </form>

                </div>
            </template>
        </div>
    </div>

</div>

<script>
function pesoInput(initialValue) {
    const fmt = v => String(v).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const initRaw = initialValue ? String(initialValue).replace(/\D/g, '') : '';
    return {
        display: initRaw ? fmt(initRaw) : '',
        raw: initRaw,
        onInput(e) {
            const digits = e.target.value.replace(/\D/g, '');
            this.raw = digits;
            this.display = fmt(digits);
        },
        onKeydown(e) {
            const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
            if (allowed.includes(e.key)) return;
            if (!/^\d$/.test(e.key)) e.preventDefault();
        },
        reset() { this.display = ''; this.raw = ''; },
    };
}

function modalMesesComp(em) {
    const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const fmtNum = v => String(Math.round(v || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const toInt  = v => parseInt(String(v ?? '').replace(/\D/g, '') || '0', 10);

    const initComp = (cia) => {
        if (!cia) return null;
        const anual = Math.round(Number(cia.anual));
        const vals = {}, displays = {};
        for (let m = 1; m <= 12; m++) {
            const v = cia.meses && cia.meses[m] ? Math.round(Number(cia.meses[m])) : 0;
            vals[m]    = v;
            displays[m] = v > 0 ? fmtNum(v) : '';
        }
        return { id: cia.id, anual, vals, displays };
    };

    return {
        asesor_id:    em.asesor_id,
        asesor_name:  em.asesor_name,
        anio:         em.anio,
        cia1:         initComp(em.cia1),
        cia2:         initComp(em.cia2),
        mesesNombres: MESES,

        fmt: fmtNum,

        get suma1() { return this.cia1 ? Object.values(this.cia1.vals).reduce((s, v) => s + (v || 0), 0) : 0; },
        get suma2() { return this.cia2 ? Object.values(this.cia2.vals).reduce((s, v) => s + (v || 0), 0) : 0; },
        get ok1()   { return !this.cia1 || Math.abs(this.suma1 - this.cia1.anual) < 1; },
        get ok2()   { return !this.cia2 || Math.abs(this.suma2 - this.cia2.anual) < 1; },
        get todoOk(){ return this.ok1 && this.ok2; },
        get diferencia1() { return this.cia1 ? this.suma1 - this.cia1.anual : 0; },
        get diferencia2() { return this.cia2 ? this.suma2 - this.cia2.anual : 0; },

        onInput(comp, m, e) {
            const digits = e.target.value.replace(/\D/g, '');
            const v = parseInt(digits || '0', 10);
            this[comp].vals[m]    = v;
            this[comp].displays[m] = fmtNum(digits);
        },
        onKeydown(e) {
            const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
            if (allowed.includes(e.key)) return;
            if (!/^\d$/.test(e.key)) e.preventDefault();
        },
        distribuirIgual(comp) {
            const c = this[comp];
            if (!c) return;
            const porMes = Math.floor(c.anual / 12);
            const resto  = c.anual - (porMes * 12);
            for (let m = 1; m <= 12; m++) {
                const v = m === 12 ? porMes + resto : porMes;
                c.vals[m]    = v;
                c.displays[m] = fmtNum(v);
            }
        },
    };
}
</script>
</x-layouts.app>
