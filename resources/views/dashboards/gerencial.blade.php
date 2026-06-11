<x-layouts.app title="Visión ejecutiva · CRM">

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
  <div class="flex items-end justify-between mb-7">
    <div>
      <p class="text-xs uppercase tracking-widest text-slate-400">Dashboard gerencial</p>
      <h1 class="text-2xl font-bold text-slate-900 mt-1">
        Visión ejecutiva {{ $anio }}
        @if($periodo !== 'anio')
          · {{ $periodo === 'mes' ? 'Mes actual' : 'Trimestre actual' }}
        @endif
      </h1>
    </div>
    <div class="flex gap-1 p-1 rounded-xl bg-white border border-slate-200">
      @foreach(['mes' => 'Mes', 'trimestre' => 'Trimestre', 'anio' => 'Año'] as $key => $label)
        <a href="{{ route('dash.gerencial', ['periodo' => $key, 'anio' => $anio]) }}"
           class="px-3 py-1.5 text-sm rounded-lg transition {{ $periodo === $key ? 'text-white' : '' }}"
           style="{{ $periodo === $key ? 'background:var(--accent)' : 'color:var(--muted)' }}">
          {{ $label }}
        </a>
      @endforeach
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
    <div class="dash-card p-5 lg:col-span-2">
      <h2 class="font-semibold text-slate-800 mb-4">Presupuesto por vendedor</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-slate-400">
              <th class="font-medium pb-2">Vendedor</th>
              <th class="font-medium pb-2 text-right">Presupuesto</th>
              <th class="font-medium pb-2 text-right">Logrado</th>
              <th class="font-medium pb-2 text-right">Cumpl.</th>
              <th class="font-medium pb-2 w-28">Avance</th>
            </tr>
          </thead>
          <tbody>
            <template x-for="v in vendedores" :key="v.asesor_id">
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
            <tr x-show="vendedores.length === 0">
              <td colspan="5" class="py-6 text-center text-sm text-slate-400">Sin datos de presupuesto para este año.</td>
            </tr>
          </tbody>
        </table>
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
                <td class="text-center py-2.5 px-2"><span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded" x-text="fila.vip_activos"></span></td>
                <td class="text-center py-2.5 px-2"><span class="text-amber-700 font-bold bg-amber-50 px-1.5 py-0.5 rounded" x-text="fila.urgentes"></span></td>
                <td class="text-center py-2.5 px-2"><span class="text-red-600 font-bold bg-red-50 px-1.5 py-0.5 rounded" x-text="fila.rescate"></span></td>
                <td class="text-center py-2.5 px-2"><span class="text-indigo-600 font-bold bg-indigo-50 px-1.5 py-0.5 rounded" x-text="fila.reactivacion"></span></td>
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
                <span class="flex items-center justify-center gap-1">P4 L.P. <span x-text="arrow('p4_largo_plazo')" class="opacity-50"></span></span>
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

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
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
