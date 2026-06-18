<x-layouts.app title="Gerencial · CRM" :hide-page-title="true">

<style>
:root{
  --ink:#1a1d23;--muted:#6b7280;--line:#e8e6e1;--paper:#f6f4ef;--card:#fffdf9;
  --green:#15803d;--green-bg:#dcfce7;--amber:#b45309;--amber-bg:#fef3c7;
  --red:#b91c1c;--red-bg:#fee2e2;--accent:#0f766e;
}
.dash-card{background:var(--card);border:1px solid var(--line);border-radius:14px}
.tnum{font-variant-numeric:tabular-nums}
</style>

<div x-data="dashGerencial()" x-init="init()">

  {{-- ===== Encabezado ===== --}}
  @php
    $ciaColors  = [0 => '#7c3aed', 1 => '#b91c1c', 2 => '#1d4ed8'];
    $ciaActual  = [0 => 'Ambas compañías', 1 => 'Formacol', 2 => 'Contiflex'][$cia];
    $periodoActual = ['mes' => 'Mes actual', 'trimestre' => 'Trimestre actual', 'anio' => 'Año completo'][$periodo];
  @endphp

  <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-6 pt-5 pb-4 mb-8">
    {{-- Fila principal --}}
    <div class="flex items-start justify-between gap-6">

      {{-- Título --}}
      <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-1">Dashboard ejecutivo</p>
        <h1 class="text-2xl font-bold text-slate-900 leading-tight">Visión gerencial <span class="text-slate-400 font-semibold">{{ $anio }}</span></h1>
        <div class="flex items-center gap-2 mt-1.5">
          <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
            <span class="w-1.5 h-1.5 rounded-full inline-block" style="background:{{ $ciaColors[$cia] }}"></span>
            {{ $ciaActual }}
          </span>
          <span class="text-slate-300">·</span>
          <span class="text-xs font-medium text-slate-500">{{ $periodoActual }}</span>
          <span class="text-slate-300">·</span>
          <span class="text-xs text-slate-400">Al {{ now()->translatedFormat('j \d\e F, Y') }}</span>
        </div>
      </div>

      {{-- Filtros --}}
      <div class="flex items-end gap-5 shrink-0">
        {{-- Compañía --}}
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5 text-center">Compañía</p>
          <div class="flex gap-1 p-1 rounded-xl bg-white border border-slate-200 shadow-sm">
            @foreach([0 => 'Ambas', 1 => 'Formacol', 2 => 'Contiflex'] as $ciaKey => $ciaLabel)
              <a href="{{ route('dash.gerencial', ['periodo' => $periodo, 'anio' => $anio, 'cia' => $ciaKey]) }}"
                 class="px-3 py-1.5 text-sm rounded-lg transition-all font-medium {{ $cia === $ciaKey ? 'text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}"
                 style="{{ $cia === $ciaKey ? 'background:'.$ciaColors[$ciaKey] : '' }}">
                {{ $ciaLabel }}
              </a>
            @endforeach
          </div>
        </div>

        {{-- Separador vertical --}}
        <div class="w-px h-8 bg-slate-200 mb-1.5"></div>

        {{-- Período --}}
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5 text-center">Período</p>
          <div class="flex gap-1 p-1 rounded-xl bg-white border border-slate-200 shadow-sm">
            @foreach(['mes' => 'Mes', 'trimestre' => 'Trimestre', 'anio' => 'Año'] as $key => $label)
              <a href="{{ route('dash.gerencial', ['periodo' => $key, 'anio' => $anio, 'cia' => $cia]) }}"
                 class="px-3 py-1.5 text-sm rounded-lg transition-all font-medium {{ $periodo === $key ? 'text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}"
                 style="{{ $periodo === $key ? 'background:var(--accent)' : '' }}">
                {{ $label }}
              </a>
            @endforeach
          </div>
        </div>

        {{-- Año --}}
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5 text-center">Año</p>
          <div class="flex gap-1 p-1 rounded-xl bg-white border border-slate-200 shadow-sm">
            @foreach([now()->year - 1, now()->year, now()->year + 1] as $y)
              <a href="{{ route('dash.gerencial', ['periodo' => $periodo, 'anio' => $y, 'cia' => $cia]) }}"
                 class="px-3 py-1.5 text-sm rounded-lg transition-all font-medium {{ $anio === $y ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $y }}
              </a>
            @endforeach
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- ===== KPIs globales ===== --}}
  <div class="grid sm:grid-cols-4 gap-4 mb-5">
    <div class="dash-card p-4">
      <p class="text-xs text-slate-500">Presupuesto total</p>
      <p class="text-2xl font-bold mt-1 tnum text-slate-900" x-text="moneyShort(totales.presupuesto)"></p>
    </div>
    <div class="dash-card p-4">
      <p class="text-xs text-slate-500">{{ $periodo === 'mes' ? 'Logrado este mes' : ($periodo === 'trimestre' ? 'Logrado este trimestre' : 'Logrado en el año') }}</p>
      <p class="text-2xl font-bold mt-1 tnum text-slate-900" x-text="moneyShort(totales.logrado)"></p>
    </div>
    <div class="dash-card p-4">
      <p class="text-xs text-slate-500">Cumplimiento</p>
      <p class="text-2xl font-bold mt-1 tnum text-slate-900" x-text="totales.cumplimiento+'%'"></p>
    </div>
    <div class="dash-card p-4">
      <p class="text-xs text-slate-500">Pipeline activo</p>
      <p class="text-2xl font-bold mt-1 tnum text-slate-900" x-text="moneyShort(totales.forecast)"></p>
    </div>
  </div>

  {{-- ===== Grid: gráficas y métricas ===== --}}
  <div class="grid lg:grid-cols-3 gap-5 mb-5">

    {{-- ===== Presupuesto por vendedor ===== --}}
    <div class="dash-card p-5 lg:col-span-2" x-data="sortableTable(vendedores, 8)">
      <h2 class="font-semibold text-slate-800 mb-4">Presupuesto por vendedor</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-slate-400">
              <th @click="sortOn('vendedor')" class="font-medium pb-2 cursor-pointer select-none hover:text-slate-600 transition-colors">
                <span class="flex items-center gap-1">Vendedor <span x-text="arrow('vendedor')" class="opacity-40"></span></span>
              </th>
              <th @click="sortOn('presupuesto_anual')" class="font-medium pb-2 text-right cursor-pointer select-none hover:text-slate-600 transition-colors">
                <span class="flex items-center justify-end gap-1">Presupuesto <span x-text="arrow('presupuesto_anual')" class="opacity-40"></span></span>
              </th>
              <th @click="sortOn('logrado_ytd')" class="font-medium pb-2 text-right cursor-pointer select-none hover:text-slate-600 transition-colors">
                <span class="flex items-center justify-end gap-1">Logrado <span x-text="arrow('logrado_ytd')" class="opacity-40"></span></span>
              </th>
              <th @click="sortOn('cumpl')" class="font-medium pb-2 text-right cursor-pointer select-none hover:text-slate-600 transition-colors">
                <span class="flex items-center justify-end gap-1">Cumpl. <span x-text="arrow('cumpl')" class="opacity-40"></span></span>
              </th>
              <th class="font-medium pb-2 w-28">Avance</th>
            </tr>
          </thead>
          <tbody>
            <template x-for="v in paged" :key="v.asesor_id">
              <tr class="border-t border-slate-100">
                <td class="py-2.5 text-slate-800" x-text="v.vendedor"></td>
                <td class="py-2.5 text-right tnum text-slate-400" x-text="moneyShort(v.presupuesto_anual)"></td>
                <td class="py-2.5 text-right tnum text-slate-800" x-text="moneyShort(v.logrado_ytd)"></td>
                <td class="py-2.5 text-right tnum">
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                        :style="semaforoStyle(v.cumpl)" x-text="v.cumpl+'%'"></span>
                </td>
                <td class="py-2.5">
                  <div class="h-2 rounded-full overflow-hidden bg-slate-100">
                    <div class="h-full rounded-full" :style="`width:${Math.min(v.cumpl,100)}%;background:${barColor(v.cumpl)}`"></div>
                  </div>
                </td>
              </tr>
            </template>
            <tr x-show="all.length === 0">
              <td colspan="5" class="py-6 text-center text-sm text-slate-400">Sin datos de presupuesto para este año.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t border-slate-100" x-show="pageCount > 1">
        <p class="text-xs text-slate-400 mr-1">
          <span x-text="(page-1)*pageSize+1"></span>–<span x-text="Math.min(page*pageSize, all.length)"></span>
          de <span x-text="all.length"></span>
        </p>
        <button @click="prevPage()" :disabled="page===1"
                class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition">‹</button>
        <span class="text-xs text-slate-500 px-1" x-text="page + ' / ' + pageCount"></span>
        <button @click="nextPage()" :disabled="page===pageCount"
                class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition">›</button>
      </div>
    </div>

    {{-- ===== Salud de cartera ===== --}}
    <div class="dash-card p-5">
      <div class="flex items-start justify-between mb-4">
        <div>
          <h2 class="font-semibold text-slate-800">Salud de cartera</h2>
          <p class="text-xs text-slate-400 mt-0.5">Al {{ \Carbon\Carbon::today()->translatedFormat('d M Y') }}</p>
        </div>
      </div>

      <div class="divide-y divide-slate-100">
        <template x-for="b in churn" :key="b.banda">
          <div class="py-2.5 flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="`background:${bandaColor(b.banda)}`"></span>
            <div class="flex-1 min-w-0">
              <span class="text-sm font-medium text-slate-800" x-text="bandaLabel(b.banda)"></span>
              <span class="text-xs text-slate-400 ml-1.5" x-text="bandaCriterio(b.banda)"></span>
            </div>
            <div class="text-right shrink-0">
              <p class="text-sm font-semibold tnum text-slate-800" x-text="moneyShort(b.valor_en_banda)"></p>
              <p class="text-xs text-slate-400" x-text="b.num_clientes + ' clientes'"></p>
            </div>
          </div>
        </template>
        <div x-show="churn.length === 0" class="py-6 text-center text-sm text-slate-400">Sin datos disponibles.</div>
      </div>

      <div class="mt-3 pt-3 border-t border-slate-200 flex justify-between items-center">
        <p class="text-xs text-slate-500">Valor en riesgo <span class="text-slate-400">(tibios + en riesgo + inactivos)</span></p>
        <span class="tnum font-bold" style="color:var(--red)" x-text="moneyShort(valorEnRiesgo)"></span>
      </div>
    </div>

    {{-- ===== Motivos de pérdida ===== --}}
    <div class="dash-card p-5">
      <h2 class="font-semibold text-slate-800 mb-4">Motivos de pérdida</h2>
      <canvas id="chartMotivos" height="200"></canvas>
      <div x-show="motivos.length === 0" class="text-center text-sm text-slate-400 py-4">Sin negocios perdidos en {{ $anio }}.</div>
    </div>

    {{-- ===== Ciclo de venta ===== --}}
    <div class="dash-card p-5">
      <h2 class="font-semibold text-slate-800 mb-4">Ciclo de venta promedio</h2>
      <div class="space-y-3">
        <template x-for="c in ciclo" :key="c.vendedor">
          <div class="flex items-center gap-3">
            <span class="text-sm w-28 truncate text-slate-700" x-text="c.vendedor"></span>
            <div class="flex-1 h-2 rounded-full overflow-hidden bg-slate-100">
              <div class="h-full rounded-full" style="background:var(--accent)"
                   :style="`width:${maxCiclo > 0 ? Math.min(c.dias/maxCiclo*100,100) : 0}%`"></div>
            </div>
            <span class="text-sm tnum w-14 text-right text-slate-700" x-text="c.dias+'d'"></span>
          </div>
        </template>
        <div x-show="ciclo.length === 0" class="text-center text-sm text-slate-400 py-4">Sin negocios ganados en {{ $anio }}.</div>
      </div>
    </div>

    {{-- ===== Actividad del equipo ===== --}}
    <div class="dash-card p-5">
      <h2 class="font-semibold text-slate-800 mb-4">Actividad del equipo</h2>
      <canvas id="chartActividad" height="200"></canvas>
      <div x-show="actividad.length === 0" class="text-center text-sm text-slate-400 py-4">Sin actividades registradas en {{ $anio }}.</div>
    </div>

  </div>

  {{-- ===== Top Asesores (paginado) ===== --}}
  @if(!empty($topAsesores))
  <div class="dash-card p-5 mb-5" x-data="asesoresCtrl(@js($topAsesores))">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-slate-800">Top Asesores</h2>
      <span class="text-xs text-slate-400" x-text="all.length + ' asesores'"></span>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
      <template x-for="(asesor, idx) in paged" :key="idx">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
          <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-extrabold shrink-0"
               :class="medalBg(idx)">
            <template x-if="gIdx(idx) === 0">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/></svg>
            </template>
            <template x-if="gIdx(idx) !== 0">
              <span :class="medalText(idx)" x-text="gIdx(idx) + 1"></span>
            </template>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-slate-900 truncate" x-text="asesor.nombre"></p>
            <p class="text-xs text-slate-400" x-text="asesor.negocios_ganados + ' negocios ganados'"></p>
          </div>
          <p class="text-sm font-bold text-emerald-600 shrink-0"
             x-text="'$' + Number(asesor.valor_ganado).toLocaleString('es-CO', {maximumFractionDigits:0})"></p>
        </div>
      </template>
    </div>
    <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100" x-show="pageCount > 1">
      <p class="text-xs text-slate-400">
        Mostrando <span x-text="(page-1)*pageSize+1"></span>–<span x-text="Math.min(page*pageSize, all.length)"></span>
        de <span x-text="all.length"></span>
      </p>
      <div class="flex items-center gap-1">
        <button @click="prevPage()" :disabled="page===1"
                class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition">‹ Ant</button>
        <span class="text-xs text-slate-500 px-2" x-text="'Pág. ' + page + ' de ' + pageCount"></span>
        <button @click="nextPage()" :disabled="page===pageCount"
                class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition">Sig ›</button>
      </div>
    </div>
  </div>
  @endif

  {{-- ===== Oportunidades de Venta Integral (paginado) ===== --}}
  @if(!empty($integrales))
  @php
    $yaIntegrales = collect($integrales)->where('perfil_cruzado', 'YA INTEGRAL')->count();
    $integralesJs = array_map(function($row) {
        $perfil = $row['perfil_cruzado'] ?? '';
        if ($perfil === 'YA INTEGRAL') {
            $row['_empresa'] = 'ambas';
        } elseif ((int)($row['COMPANIA'] ?? 0) === 1) {
            $row['_empresa'] = 'formacol';
        } else {
            $row['_empresa'] = 'contiflex';
        }
        return $row;
    }, $integrales);
  @endphp


  <div class="dash-card mb-5" x-data="sortableTable(@js($integralesJs), 10)">
    <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-purple-500 to-violet-500"></div>
    <div class="p-5">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="font-semibold text-slate-800">Oportunidades de Venta Integral</h2>
          <p class="text-xs text-slate-400 mt-0.5">Perfil de compra entre compañías · clientes VIP y urgentes</p>
        </div>
        <span class="text-xs bg-purple-100 text-purple-700 border border-purple-200 px-2.5 py-1 rounded-lg font-bold">{{ count($integrales) }} clientes</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/70">
              <th @click="sortOn('RAZON_SOCIAL')" :class="sortBy==='RAZON_SOCIAL'?'text-purple-700':'text-slate-500'" class="text-left py-2.5 pr-3 pl-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center gap-1">Cliente <span x-text="arrow('RAZON_SOCIAL')" class="opacity-50"></span></span></th>
              <th @click="sortOn('NOMBRE_VENDEDOR')" :class="sortBy==='NOMBRE_VENDEDOR'?'text-purple-700':'text-slate-500'" class="text-left py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center gap-1">Vendedor <span x-text="arrow('NOMBRE_VENDEDOR')" class="opacity-50"></span></span></th>
              <th @click="sortOn('HORIZONTE_PRESUPUESTO')" :class="sortBy==='HORIZONTE_PRESUPUESTO'?'text-purple-700':'text-slate-500'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-center gap-1">Tipo <span x-text="arrow('HORIZONTE_PRESUPUESTO')" class="opacity-50"></span></span></th>
              <th @click="sortOn('perfil_cruzado')" :class="sortBy==='perfil_cruzado'?'text-purple-700':'text-purple-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-center gap-1">Perfil cruzado <span x-text="arrow('perfil_cruzado')" class="opacity-50"></span></span></th>
              <th @click="sortOn('VLR_NETO_FACTURADO')" :class="sortBy==='VLR_NETO_FACTURADO'?'text-slate-800':'text-slate-500'" class="text-right py-2.5 pr-3 pl-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-end gap-1">Facturado <span x-text="arrow('VLR_NETO_FACTURADO')" class="opacity-50"></span></span></th>
              <th @click="sortOn('DIAS_DESDE_ULTIMA_COMPRA')" :class="sortBy==='DIAS_DESDE_ULTIMA_COMPRA'?'text-slate-800':'text-slate-500'" class="text-right py-2.5 pl-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-end gap-1">Días <span x-text="arrow('DIAS_DESDE_ULTIMA_COMPRA')" class="opacity-50"></span></span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <template x-for="(cli, idx) in paged" :key="idx">
              <tr class="hover:bg-slate-50 transition-colors">
                <td class="py-2.5 pr-3 pl-3"><p class="font-semibold text-slate-900" x-text="cli.RAZON_SOCIAL"></p><p class="text-slate-400" x-text="cli.CIUDAD"></p></td>
                <td class="py-2.5 px-2 text-slate-500 whitespace-nowrap" x-text="cli.NOMBRE_VENDEDOR"></td>
                <td class="py-2.5 px-2 text-center">
                  <span class="border px-1.5 py-0.5 rounded-md font-bold" :class="(cli.HORIZONTE_PRESUPUESTO||'').startsWith('P1')?'bg-emerald-100 text-emerald-700 border-emerald-200':'bg-orange-100 text-orange-700 border-orange-200'" x-text="(cli.HORIZONTE_PRESUPUESTO||'').startsWith('P1')?'P1 Activo':'P2 Riesgo'"></span>
                </td>
                <td class="py-2.5 px-2 text-center">
                  <span class="px-1.5 py-0.5 rounded-md font-semibold border"
                    :style="cli._empresa==='ambas'?'background:#f3e8ff;color:#7e22ce;border-color:#e9d5ff':(cli._empresa==='formacol'?'background:#fee2e2;color:#b91c1c;border-color:#fecaca':'background:#dbeafe;color:#1d4ed8;border-color:#bfdbfe')"
                    x-text="cli._empresa==='ambas'?'Ambas':(cli._empresa==='formacol'?'Formacol':'Contiflex')"></span>
                </td>
                <td class="py-2.5 pl-3 pr-3 text-right font-bold text-slate-700 whitespace-nowrap" x-text="fmt(cli.VLR_NETO_FACTURADO)"></td>
                <td class="py-2.5 pl-2 text-right font-semibold whitespace-nowrap" :class="(parseInt(cli.DIAS_DESDE_ULTIMA_COMPRA)||0)>60?'text-red-600':'text-slate-500'" x-text="cli.DIAS_DESDE_ULTIMA_COMPRA??'-'"></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
        @if(count($integrales) - $yaIntegrales > 0)
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-purple-400"></span><span class="text-xs text-slate-500"><span class="font-bold text-purple-700">{{ count($integrales) - $yaIntegrales }}</span> con potencial cross-selling</span></div>
          <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-300"></span><span class="text-xs text-slate-500"><span class="font-bold text-slate-700">{{ $yaIntegrales }}</span> ya compran en ambas compañías</span></div>
        </div>
        @else
        <div></div>
        @endif
        <div class="flex items-center gap-2" x-show="pageCount > 1">
          <p class="text-xs text-slate-400">
            <span x-text="(page-1)*pageSize+1"></span>–<span x-text="Math.min(page*pageSize, all.length)"></span>
            de <span x-text="all.length"></span>
          </p>
          <div class="flex items-center gap-1">
            <button @click="prevPage()" :disabled="page===1"
                    class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition">‹</button>
            <span class="text-xs text-slate-500 px-1" x-text="page + ' / ' + pageCount"></span>
            <button @click="nextPage()" :disabled="page===pageCount"
                    class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition">›</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  {{-- ===== Panorama Gerencial (paginado) ===== --}}
  @if(!empty($panoramaGerencial))
  <div class="dash-card mb-5" x-data="sortableTable(@js($panoramaGerencial), 10)">
    <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-slate-500 to-slate-400"></div>
    <div class="p-5">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="font-semibold text-slate-800">Panorama Gerencial</h2>
          <p class="text-xs text-slate-400 mt-0.5">Clasificación de cartera por vendedor</p>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/70">
              <th @click="sortOn('NOMBRE_VENDEDOR')" :class="sortBy==='NOMBRE_VENDEDOR'?'text-slate-800':'text-slate-500'" class="text-left py-2.5 pr-4 pl-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center gap-1">Vendedor <span x-text="arrow('NOMBRE_VENDEDOR')" class="opacity-50"></span></span></th>
              <th @click="sortOn('total_clientes')" :class="sortBy==='total_clientes'?'text-slate-800':'text-slate-500'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-center gap-1">Total <span x-text="arrow('total_clientes')" class="opacity-50"></span></span></th>
              <th @click="sortOn('vip_activos')" :class="sortBy==='vip_activos'?'text-emerald-700':'text-emerald-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-center gap-1">VIP <span x-text="arrow('vip_activos')" class="opacity-50"></span></span></th>
              <th @click="sortOn('urgentes')" :class="sortBy==='urgentes'?'text-amber-700':'text-amber-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-center gap-1">Urgente <span x-text="arrow('urgentes')" class="opacity-50"></span></span></th>
              <th @click="sortOn('rescate')" :class="sortBy==='rescate'?'text-red-600':'text-red-500'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-center gap-1">Rescate <span x-text="arrow('rescate')" class="opacity-50"></span></span></th>
              <th @click="sortOn('reactivacion')" :class="sortBy==='reactivacion'?'text-indigo-600':'text-indigo-500'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-center gap-1">Reactiv. <span x-text="arrow('reactivacion')" class="opacity-50"></span></span></th>
              <th @click="sortOn('facturacion_total')" :class="sortBy==='facturacion_total'?'text-slate-800':'text-slate-500'" class="text-right py-2.5 pl-4 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-end gap-1">Facturación <span x-text="arrow('facturacion_total')" class="opacity-50"></span></span></th>
              <th @click="sortOn('valor_en_riesgo')" :class="sortBy==='valor_en_riesgo'?'text-slate-800':'text-slate-500'" class="text-right py-2.5 pl-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-end gap-1">En riesgo <span x-text="arrow('valor_en_riesgo')" class="opacity-50"></span></span></th>
              <th @click="sortOn('porc_riesgo')" :class="sortBy==='porc_riesgo'?'text-slate-800':'text-slate-500'" class="text-right py-2.5 pl-2 pr-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors"><span class="flex items-center justify-end gap-1">% Riesgo <span x-text="arrow('porc_riesgo')" class="opacity-50"></span></span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <template x-for="(fila, idx) in paged" :key="idx">
              <tr class="hover:bg-slate-50 transition-colors">
                <td class="py-2.5 pr-4 pl-3 font-semibold text-slate-800 whitespace-nowrap"><span x-text="fila.NOMBRE_VENDEDOR"></span><span x-show="fila.COMPANIA" class="text-slate-400 font-normal" x-text="' · Cía '+(fila.COMPANIA||'')"></span></td>
                <td class="text-center py-2.5 px-2 text-slate-600 font-medium" x-text="fila.total_clientes"></td>
                <td class="text-center py-2.5 px-2">
                  <button @click="$dispatch('abrir-panorama', {vendedor: fila.NOMBRE_VENDEDOR, horizonte: 'P1', cia: {{ $cia }}})"
                          class="font-bold px-1.5 py-0.5 rounded transition cursor-pointer"
                          :class="(fila.vip_activos||0)>0 ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' : 'text-slate-300 bg-slate-50 hover:bg-slate-100'"
                          x-text="fila.vip_activos ?? 0"></button>
                </td>
                <td class="text-center py-2.5 px-2">
                  <button @click="$dispatch('abrir-panorama', {vendedor: fila.NOMBRE_VENDEDOR, horizonte: 'P2', cia: {{ $cia }}})"
                          class="font-bold px-1.5 py-0.5 rounded transition cursor-pointer"
                          :class="(fila.urgentes||0)>0 ? 'text-amber-700 bg-amber-50 hover:bg-amber-100' : 'text-slate-300 bg-slate-50 hover:bg-slate-100'"
                          x-text="fila.urgentes ?? 0"></button>
                </td>
                <td class="text-center py-2.5 px-2">
                  <button @click="$dispatch('abrir-panorama', {vendedor: fila.NOMBRE_VENDEDOR, horizonte: 'P3', cia: {{ $cia }}})"
                          class="font-bold px-1.5 py-0.5 rounded transition cursor-pointer"
                          :class="(fila.rescate||0)>0 ? 'text-red-600 bg-red-50 hover:bg-red-100' : 'text-slate-300 bg-slate-50 hover:bg-slate-100'"
                          x-text="fila.rescate ?? 0"></button>
                </td>
                <td class="text-center py-2.5 px-2">
                  <button @click="$dispatch('abrir-panorama', {vendedor: fila.NOMBRE_VENDEDOR, horizonte: 'P4', cia: {{ $cia }}})"
                          class="font-bold px-1.5 py-0.5 rounded transition cursor-pointer"
                          :class="(fila.reactivacion||0)>0 ? 'text-indigo-600 bg-indigo-50 hover:bg-indigo-100' : 'text-slate-300 bg-slate-50 hover:bg-slate-100'"
                          x-text="fila.reactivacion ?? 0"></button>
                </td>
                <td class="text-right py-2.5 pl-4 text-slate-600 whitespace-nowrap font-medium" x-text="fmt(fila.facturacion_total)"></td>
                <td class="text-right py-2.5 pl-3 text-amber-600 whitespace-nowrap font-semibold" x-text="fmt(fila.valor_en_riesgo)"></td>
                <td class="text-right py-2.5 pl-2 pr-3" :class="(parseFloat(fila.porc_riesgo)||0)>=40?'text-red-600 font-bold':((parseFloat(fila.porc_riesgo)||0)>=20?'text-amber-600 font-semibold':'text-slate-500')" x-text="(parseFloat(fila.porc_riesgo)||0).toFixed(1)+'%'"></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t border-slate-100" x-show="pageCount > 1">
        <p class="text-xs text-slate-400 mr-1">
          <span x-text="(page-1)*pageSize+1"></span>–<span x-text="Math.min(page*pageSize, all.length)"></span>
          de <span x-text="all.length"></span>
        </p>
        <button @click="prevPage()" :disabled="page===1"
                class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition">‹</button>
        <span class="text-xs text-slate-500 px-1" x-text="page + ' / ' + pageCount"></span>
        <button @click="nextPage()" :disabled="page===pageCount"
                class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition">›</button>
      </div>
    </div>
  </div>
  @endif

  {{-- ===== Panorama Presupuestal por Vendedor ===== --}}
  @if(!empty($panoramaPresupuestal))
  <div class="dash-card mb-5" x-data="sortableTable(@js($panoramaPresupuestal), 10)">
    <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-teal-600 to-teal-400"></div>
    <div class="p-5">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="font-semibold text-slate-800">Panorama Presupuestal por Vendedor</h2>
          <p class="text-xs text-slate-400 mt-0.5">Facturación {{ $anio }} vs {{ $anio - 1 }} · distribución de cartera por prioridad</p>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/70">
              <th @click="sortOn('NOMBRE_VENDEDOR')" :class="sortBy==='NOMBRE_VENDEDOR'?'text-teal-700':'text-slate-500'" class="text-left py-2.5 pr-3 pl-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                <span class="flex items-center gap-1">Vendedor <span x-text="arrow('NOMBRE_VENDEDOR')" class="opacity-50"></span></span>
              </th>
              <th @click="sortOn('facturado_anio_actual')" :class="sortBy==='facturado_anio_actual'?'text-teal-800':'text-teal-600'" class="text-right py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                <span class="flex items-center justify-end gap-1">{{ $anio }} <span x-text="arrow('facturado_anio_actual')" class="opacity-50"></span></span>
              </th>
              <th @click="sortOn('facturado_anio_anterior')" :class="sortBy==='facturado_anio_anterior'?'text-slate-800':'text-slate-500'" class="text-right py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                <span class="flex items-center justify-end gap-1">{{ $anio - 1 }} <span x-text="arrow('facturado_anio_anterior')" class="opacity-50"></span></span>
              </th>
              <th @click="sortOn('variacion_porc')" :class="sortBy==='variacion_porc'?'text-slate-800':'text-slate-500'" class="text-right py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                <span class="flex items-center justify-end gap-1">Var.% <span x-text="arrow('variacion_porc')" class="opacity-50"></span></span>
              </th>
              <th @click="sortOn('p1_activos')" :class="sortBy==='p1_activos'?'text-emerald-700':'text-emerald-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                <span class="flex items-center justify-center gap-1">P1 Activos <span x-text="arrow('p1_activos')" class="opacity-50"></span></span>
              </th>
              <th @click="sortOn('p2_en_riesgo')" :class="sortBy==='p2_en_riesgo'?'text-orange-700':'text-orange-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                <span class="flex items-center justify-center gap-1">P2 En riesgo <span x-text="arrow('p2_en_riesgo')" class="opacity-50"></span></span>
              </th>
              <th @click="sortOn('p3_recuperar')" :class="sortBy==='p3_recuperar'?'text-blue-700':'text-blue-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                <span class="flex items-center justify-center gap-1">P3 Recuperar <span x-text="arrow('p3_recuperar')" class="opacity-50"></span></span>
              </th>
              <th @click="sortOn('p4_largo_plazo')" :class="sortBy==='p4_largo_plazo'?'text-slate-700':'text-slate-500'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                <span class="flex items-center justify-center gap-1">P4 Inactivos <span x-text="arrow('p4_largo_plazo')" class="opacity-50"></span></span>
              </th>
              <th @click="sortOn('valor_a_rescatar_p2')" :class="sortBy==='valor_a_rescatar_p2'?'text-orange-700':'text-orange-600'" class="text-right py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                <span class="flex items-center justify-end gap-1">$ Rescatar P2 <span x-text="arrow('valor_a_rescatar_p2')" class="opacity-50"></span></span>
              </th>
              <th @click="sortOn('valor_potencial_p3')" :class="sortBy==='valor_potencial_p3'?'text-blue-700':'text-blue-600'" class="text-right py-2.5 pl-2 pr-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                <span class="flex items-center justify-end gap-1">$ Potencial P3 <span x-text="arrow('valor_potencial_p3')" class="opacity-50"></span></span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <template x-for="(fila, idx) in paged" :key="idx">
              <tr class="hover:bg-slate-50 transition-colors">
                <td class="py-2.5 pr-3 pl-3 font-semibold text-slate-800 whitespace-nowrap" x-text="fila.NOMBRE_VENDEDOR"></td>
                <td class="text-right py-2.5 px-2 font-bold text-teal-700 whitespace-nowrap" x-text="fmt(fila.facturado_anio_actual)"></td>
                <td class="text-right py-2.5 px-2 text-slate-500 whitespace-nowrap" x-text="fmt(fila.facturado_anio_anterior)"></td>
                <td class="text-right py-2.5 px-2 font-bold whitespace-nowrap"
                    :class="(parseFloat(fila.variacion_porc)||0) >= 0 ? 'text-emerald-600' : 'text-red-600'"
                    x-text="((parseFloat(fila.variacion_porc)||0) >= 0 ? '+' : '') + (parseFloat(fila.variacion_porc)||0).toFixed(1) + '%'"></td>
                <td class="text-center py-2.5 px-2"><span class="bg-emerald-100 text-emerald-700 font-bold px-1.5 py-0.5 rounded" x-text="fila.p1_activos"></span></td>
                <td class="text-center py-2.5 px-2"><span class="bg-orange-100 text-orange-700 font-bold px-1.5 py-0.5 rounded" x-text="fila.p2_en_riesgo"></span></td>
                <td class="text-center py-2.5 px-2"><span class="bg-blue-100 text-blue-700 font-bold px-1.5 py-0.5 rounded" x-text="fila.p3_recuperar"></span></td>
                <td class="text-center py-2.5 px-2"><span class="bg-slate-100 text-slate-600 font-bold px-1.5 py-0.5 rounded" x-text="fila.p4_largo_plazo"></span></td>
                <td class="text-right py-2.5 px-2 text-orange-600 font-semibold whitespace-nowrap" x-text="fmt(fila.valor_a_rescatar_p2)"></td>
                <td class="text-right py-2.5 pl-2 pr-3 text-blue-600 font-semibold whitespace-nowrap" x-text="fmt(fila.valor_potencial_p3)"></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t border-slate-100" x-show="pageCount > 1">
        <p class="text-xs text-slate-400 mr-1">
          <span x-text="(page-1)*pageSize+1"></span>–<span x-text="Math.min(page*pageSize, all.length)"></span>
          de <span x-text="all.length"></span>
        </p>
        <button @click="prevPage()" :disabled="page===1"
                class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition">‹</button>
        <span class="text-xs text-slate-500 px-1" x-text="page + ' / ' + pageCount"></span>
        <button @click="nextPage()" :disabled="page===pageCount"
                class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition">›</button>
      </div>
    </div>
  </div>
  @endif

</div>

{{-- ===== Modal detalle clientes por vendedor/horizonte ===== --}}
<div
  x-data="panoramaModalCtrl()"
  @abrir-panorama.window="abrir($event.detail)"
  x-show="abierto"
  x-transition:enter="transition ease-out duration-250"
  x-transition:enter-start="opacity-0 scale-95"
  x-transition:enter-end="opacity-100 scale-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100 scale-100"
  x-transition:leave-end="opacity-0 scale-95"
  class="fixed inset-0 z-50 flex items-center justify-center p-6"
  style="display:none">

  {{-- Fondo oscuro --}}
  <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" @click="cerrar()"></div>

  {{-- Panel --}}
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">

    {{-- Header con gradiente según categoría --}}
    <div :class="{
      'from-emerald-600 to-emerald-500': horizonte==='P1',
      'from-amber-500   to-amber-400':   horizonte==='P2',
      'from-red-600     to-red-500':     horizonte==='P3',
      'from-indigo-600  to-indigo-500':  horizonte==='P4',
    }" class="bg-gradient-to-r px-8 py-6 flex items-center justify-between gap-4">

      {{-- Ícono + textos --}}
      <div class="flex items-center gap-5 min-w-0">
        <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
          {{-- P1 VIP: estrella --}}
          <svg x-show="horizonte==='P1'" class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
          {{-- P2 Urgente: triángulo de alerta --}}
          <svg x-show="horizonte==='P2'" class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          {{-- P3 Rescate: salvavidas --}}
          <svg x-show="horizonte==='P3'" class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke-width="2"/>
            <circle cx="12" cy="12" r="4"  stroke-width="2"/>
            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76" stroke-width="2" stroke-linecap="round"/>
            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07" stroke-width="2" stroke-linecap="round"/>
            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24" stroke-width="2" stroke-linecap="round"/>
            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93" stroke-width="2" stroke-linecap="round"/>
          </svg>
          {{-- P4 Reactivación: rayo --}}
          <svg x-show="horizonte==='P4'" class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-white/70 text-xs font-semibold uppercase tracking-widest"
             x-text="etiquetas[horizonte]?.categoria ?? ''"></p>
          <h3 class="text-white font-bold text-xl leading-tight truncate mt-0.5"
              x-text="vendedor"></h3>
          <div class="mt-2 flex items-center gap-2">
            <span x-show="!cargando && !error"
                  class="inline-flex items-center gap-1.5 bg-white/25 text-white text-xs font-semibold px-3 py-1 rounded-full">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20H7a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 8h6M9 16h4"/>
              </svg>
              <span x-text="clientes.length + ' cliente' + (clientes.length !== 1 ? 's' : '')"></span>
            </span>
            <span x-show="cargando" class="text-white/70 text-sm">Cargando...</span>
          </div>
        </div>
      </div>

      {{-- Botón cerrar --}}
      <button @click="cerrar()"
              class="flex-shrink-0 text-white/70 hover:text-white hover:bg-white/15 transition p-2 rounded-xl">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    {{-- Cuerpo scrolleable --}}
    <div class="flex-1 overflow-y-auto">

      {{-- Spinner --}}
      <div x-show="cargando" class="flex flex-col items-center justify-center py-24 gap-3">
        <svg class="animate-spin w-9 h-9 text-slate-300" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
        <p class="text-slate-400 text-sm">Consultando ERP...</p>
      </div>

      {{-- Error --}}
      <div x-show="error && !cargando" class="flex flex-col items-center justify-center py-24 gap-3">
        <svg class="w-10 h-10 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <p class="text-red-400 text-sm font-medium" x-text="error"></p>
      </div>

      {{-- Tabla de clientes --}}
      <div x-show="!cargando && !error && clientes.length > 0">
        <table class="w-full">
          <thead>
            <tr class="border-b-2 border-slate-100 bg-slate-50/80 sticky top-0">
              <th class="text-left py-3.5 px-8 text-xs font-semibold text-slate-400 uppercase tracking-wider">Cliente</th>
              <th class="text-right py-3.5 px-5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Fact. {{ now()->year }}</th>
              <th class="text-right py-3.5 px-5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Fact. {{ now()->year - 1 }}</th>
              <th class="text-center py-3.5 px-5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Días sin comprar</th>
              <th class="text-left py-3.5 px-5 pr-8 text-xs font-semibold text-slate-400 uppercase tracking-wider">Acción sugerida</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <template x-for="(c, i) in clientes" :key="i">
              <tr class="hover:bg-slate-50/60 transition-colors">

                {{-- Cliente: nombre + NIT + ciudad --}}
                <td class="py-4 px-8">
                  <p class="font-semibold text-slate-800 text-sm leading-snug" x-text="c.RAZON_SOCIAL"></p>
                  <p class="text-slate-400 text-xs mt-0.5 font-mono">
                    <span x-text="c.NIT"></span>
                    <template x-if="c.CIUDAD">
                      <span class="not-italic font-sans"> · <span x-text="c.CIUDAD"></span></span>
                    </template>
                  </p>
                </td>

                {{-- Facturado año actual --}}
                <td class="py-4 px-5 text-right whitespace-nowrap">
                  <p class="font-semibold text-slate-800 text-sm" x-text="fmt(c.FACTURADO_ANIO_ACTUAL)"></p>
                  <p class="text-xs mt-0.5"
                     :class="(parseFloat(c.FACTURADO_ANIO_ACTUAL)||0) >= (parseFloat(c.FACTURADO_ANIO_ANTERIOR)||0) ? 'text-emerald-500' : 'text-red-400'"
                     x-text="(parseFloat(c.FACTURADO_ANIO_ACTUAL)||0) >= (parseFloat(c.FACTURADO_ANIO_ANTERIOR)||0) ? '▲ vs año ant.' : '▼ vs año ant.'">
                  </p>
                </td>

                {{-- Facturado año anterior --}}
                <td class="py-4 px-5 text-right whitespace-nowrap">
                  <p class="text-slate-400 text-sm" x-text="fmt(c.FACTURADO_ANIO_ANTERIOR)"></p>
                </td>

                {{-- Días sin comprar --}}
                <td class="py-4 px-5 text-center">
                  <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold"
                        :class="{
                          'bg-red-50 text-red-600':     (c.DIAS_DESDE_ULTIMA_COMPRA||0) > 180,
                          'bg-amber-50 text-amber-600': (c.DIAS_DESDE_ULTIMA_COMPRA||0) > 90 && (c.DIAS_DESDE_ULTIMA_COMPRA||0) <= 180,
                          'bg-emerald-50 text-emerald-600': (c.DIAS_DESDE_ULTIMA_COMPRA||0) <= 90,
                        }"
                        x-text="c.DIAS_DESDE_ULTIMA_COMPRA != null ? c.DIAS_DESDE_ULTIMA_COMPRA + 'd' : '—'">
                  </span>
                </td>

                {{-- Acción sugerida --}}
                <td class="py-4 px-5 pr-8">
                  <p class="text-slate-500 text-sm leading-snug" x-text="c.ACCION_PRESUPUESTAL || '—'"></p>
                </td>

              </tr>
            </template>
          </tbody>
        </table>
      </div>

      {{-- Sin resultados --}}
      <div x-show="!cargando && !error && clientes.length === 0"
           class="flex flex-col items-center justify-center py-24 gap-3">
        <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6M9 16h4M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-slate-400 text-sm">No hay clientes en esta categoría.</p>
      </div>

    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
function panoramaModalCtrl() {
  return {
    abierto: false,
    cargando: false,
    vendedor: '',
    horizonte: '',
    cia: 0,
    clientes: [],
    error: null,
    etiquetas: {
      P1: { categoria: 'VIP · Presupuesto Activo'            },
      P2: { categoria: 'Urgente · Presupuesto en Riesgo'     },
      P3: { categoria: 'Rescate · Recuperar cliente'         },
      P4: { categoria: 'Reactivación · Fuera de Presupuesto' },
    },
    async abrir({ vendedor, horizonte, cia }) {
      this.vendedor  = vendedor;
      this.horizonte = horizonte;
      this.cia       = cia ?? 0;
      this.abierto   = true;
      this.cargando  = true;
      this.clientes  = [];
      this.error     = null;
      try {
        const url = `/gerencial/clientes-panorama?vendedor=${encodeURIComponent(vendedor)}&horizonte=${horizonte}&cia=${this.cia}`;
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error();
        const data = await res.json();
        this.clientes = Array.isArray(data) ? data : [];
      } catch {
        this.error = 'No se pudo cargar la información. Intenta de nuevo.';
      } finally {
        this.cargando = false;
      }
    },
    cerrar() { this.abierto = false; this.clientes = []; this.error = null; },
    fmt(n)   { return '$' + (parseFloat(n) || 0).toLocaleString('es-CO', { maximumFractionDigits: 0 }); },
  };
}

function sortableTable(items, pageSize = 10) {
  return {
    all: items,
    sortBy: null, sortDir: 1,
    page: 1, pageSize,
    get sorted() {
      if (!this.sortBy) return this.all;
      return [...this.all].sort((a, b) => {
        let x = a[this.sortBy] ?? '', y = b[this.sortBy] ?? '';
        const nx = parseFloat(x), ny = parseFloat(y);
        if (!isNaN(nx) && !isNaN(ny)) { x = nx; y = ny; }
        else { x = String(x).toLowerCase(); y = String(y).toLowerCase(); }
        return (x < y ? -1 : x > y ? 1 : 0) * this.sortDir;
      });
    },
    get pageCount() { return Math.max(1, Math.ceil(this.all.length / this.pageSize)); },
    get paged() {
      const s = (this.page - 1) * this.pageSize;
      return this.sorted.slice(s, s + this.pageSize);
    },
    sortOn(f) { this.sortBy === f ? this.sortDir *= -1 : (this.sortBy = f, this.sortDir = 1); this.page = 1; },
    prevPage() { if (this.page > 1) this.page--; },
    nextPage() { if (this.page < this.pageCount) this.page++; },
    fmt(n)    { return '$' + (parseFloat(n) || 0).toLocaleString('es-CO', { maximumFractionDigits: 0 }); },
    arrow(f)  { return this.sortBy !== f ? '↕' : this.sortDir === 1 ? '↑' : '↓'; },
  };
}

function asesoresCtrl(items) {
  return {
    all: items,
    page: 1, pageSize: 9,
    get pageCount() { return Math.max(1, Math.ceil(this.all.length / this.pageSize)); },
    get paged() {
      const s = (this.page - 1) * this.pageSize;
      return this.all.slice(s, s + this.pageSize);
    },
    gIdx(i) { return (this.page - 1) * this.pageSize + i; },
    prevPage() { if (this.page > 1) this.page--; },
    nextPage() { if (this.page < this.pageCount) this.page++; },
    medalBg(i) {
      const gi = this.gIdx(i);
      if (gi === 0) return 'bg-gradient-to-b from-yellow-300 to-yellow-500 ring-2 ring-yellow-300 shadow-md shadow-yellow-200';
      if (gi === 1) return 'bg-gradient-to-b from-slate-300 to-slate-400 ring-2 ring-slate-300 shadow-md shadow-slate-200';
      if (gi === 2) return 'bg-gradient-to-b from-orange-300 to-orange-500 ring-2 ring-orange-300 shadow-md shadow-orange-200';
      return 'bg-slate-100 shadow-sm';
    },
    medalText(i) { return this.gIdx(i) <= 2 ? 'text-white' : 'text-slate-600'; },
  };
}

function dashGerencial(){
  return {
    vendedores: @json($vendedores),
    churn:      @json($churn),
    motivos:    @json($motivos),
    ciclo:      @json($ciclo),
    actividad:  @json($actividad),

    get totales(){
      const pr = this.vendedores.reduce((s,v)=>s+v.presupuesto_anual,0);
      const lo = this.vendedores.reduce((s,v)=>s+v.logrado_ytd,0);
      const fc = this.vendedores.reduce((s,v)=>s+v.forecast_pipeline,0);
      return { presupuesto:pr, logrado:lo, cumplimiento: pr>0?Math.round(lo/pr*100):0, forecast:lo+fc };
    },
    get valorEnRiesgo(){ return this.churn.filter(b=>b.banda>='2').reduce((s,b)=>s+Number(b.valor_en_banda),0) },
    get maxCiclo(){ return this.ciclo.length ? Math.max(...this.ciclo.map(c=>c.dias),1) : 1 },

    money(v){ return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(v||0) },
    moneyShort(v){ if(!v) return '$0'; v=Number(v); if(v>=1e9) return '$'+(v/1e9).toFixed(1)+'B'; if(v>=1e6) return '$'+(v/1e6).toFixed(0)+'M'; return '$'+v },
    barColor(p){ return p>=80?'var(--green)':p>=50?'var(--amber)':'var(--red)' },
    semaforoStyle(p){ return p>=80?'background:var(--green-bg);color:var(--green)':p>=50?'background:var(--amber-bg);color:var(--amber)':'background:var(--red-bg);color:var(--red)' },
    bandaColor(b){ return {'1':'#15803d','2':'#0ea5e9','3':'#f59e0b','4':'#b91c1c'}[b[0]]||'#94a3b8' },
    bandaPct(v){ const max=this.churn.length?Math.max(...this.churn.map(b=>Number(b.valor_en_banda)),1):1; return Math.round(Number(v)/max*100) },
    bandaLabel(b){ return {'1':'Activos','2':'Tibios','3':'En riesgo','4':'Inactivos'}[b[0]]||b },
    bandaCriterio(b){ return {'1':'(compraron hace ≤ 90 días)','2':'(91 – 180 días)','3':'(181 días – 1 año)','4':'(más de 1 año sin comprar)'}[b[0]]||'' },
    bandaRefAnio(b){ return b[0]<='2' ? 'Ref: facturado {{ date("Y") }}' : 'Ref: facturado {{ date("Y") - 1 }} (último año activo)' },

    init(){
      if(this.motivos.length){
        new Chart(document.getElementById('chartMotivos'),{
          type:'doughnut',
          data:{
            labels: this.motivos.map(m=>m.motivo||'Sin motivo'),
            datasets:[{ data:this.motivos.map(m=>m.valor), backgroundColor:this.motivos.map(m=>m.color||'#94a3b8'), borderWidth:0 }]
          },
          options:{ plugins:{legend:{position:'bottom',labels:{font:{size:11},boxWidth:10}}}, cutout:'62%' }
        });
      }
      if(this.actividad.length){
        new Chart(document.getElementById('chartActividad'),{
          type:'bar',
          data:{
            labels: this.actividad.map(a=>a.vendedor),
            datasets:[
              {label:'Llamadas',  data:this.actividad.map(a=>a.llamada||0),   backgroundColor:'#0f766e'},
              {label:'Visitas',   data:this.actividad.map(a=>a.visita||0),    backgroundColor:'#0ea5e9'},
              {label:'Reuniones', data:this.actividad.map(a=>a.reunion||0),   backgroundColor:'#8b5cf6'},
              {label:'WhatsApp',  data:this.actividad.map(a=>a.whatsapp||0),  backgroundColor:'#22c55e'},
              {label:'Emails',    data:this.actividad.map(a=>a.email||0),     backgroundColor:'#cbd5e1'},
              {label:'Otros',     data:this.actividad.map(a=>a.otro||0),      backgroundColor:'#f59e0b'},
            ]
          },
          options:{
            responsive:true,
            scales:{x:{stacked:true,grid:{display:false}},y:{stacked:true,grid:{color:'#f1f5f9'}}},
            plugins:{legend:{position:'bottom',labels:{font:{size:11},boxWidth:10}}}
          }
        });
      }
    },
  }
}
</script>
@endpush
</x-layouts.app>
