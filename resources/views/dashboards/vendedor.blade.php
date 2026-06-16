<x-layouts.app title="Mi desempeño · CRM">

<div x-data="dashVendedor()" x-init="init()" class="space-y-6">

  {{-- ===== Encabezado ===== --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Asesor comercial</p>
      <h1 class="text-2xl font-bold text-slate-900 mt-0.5">
        {{ auth()->user()->name }}
      </h1>
      <p class="text-sm text-slate-500 mt-0.5">
        @if($periodo === 'mes') {{ now()->locale('es')->translatedFormat('F Y') }}
        @elseif($periodo === 'trimestre') Trimestre {{ (int) ceil(now()->month / 3) }} · {{ $anio }}
        @else Año {{ $anio }}
        @endif
      </p>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
      @if(count($companias) > 1)
      <div class="flex gap-1 p-1 rounded-xl bg-white border border-slate-200 shadow-sm">
        @foreach($companias as $cia)
          <a href="{{ request()->fullUrlWithQuery(['compania' => $cia]) }}"
             class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all
             {{ $compania === $cia
                 ? 'bg-violet-600 text-white shadow-sm'
                 : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
            Cía {{ $cia }}
          </a>
        @endforeach
      </div>
      @endif

      <div class="flex gap-1 p-1 rounded-xl bg-white border border-slate-200 shadow-sm">
        @foreach([['anio','Año'],['trimestre','Trimestre'],['mes','Mes']] as [$val,$label])
          <a href="{{ request()->fullUrlWithQuery(['periodo' => $val]) }}"
             class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all
             {{ $periodo === $val
                 ? 'bg-blue-600 text-white shadow-sm'
                 : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
            {{ $label }}
          </a>
        @endforeach
      </div>
    </div>
  </div>

  {{-- ===== Tarjeta hero: presupuesto vs logrado ===== --}}
  @if($kpi['ok'] ?? false)
  <x-ui.card class="p-6">
    <div class="flex flex-wrap items-start justify-between gap-6">
      <div class="min-w-[220px]">
        <div class="flex items-center gap-2 mb-2">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Presupuesto anual vs logrado</p>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                :class="{
                  'bg-green-100 text-green-700': kpi.semaforo === 'verde',
                  'bg-amber-100 text-amber-700': kpi.semaforo === 'amarillo',
                  'bg-red-100 text-red-700':    kpi.semaforo === 'rojo',
                  'bg-slate-100 text-slate-500': kpi.semaforo === 'sin_presupuesto',
                }"
                x-text="semaforoLabel(kpi.semaforo)"></span>
        </div>
        <p class="text-4xl font-bold tabular-nums text-slate-900" x-text="money(kpi.logrado_ytd)"></p>
        <p class="text-sm mt-1 text-slate-500">
          de <span class="tabular-nums" x-text="money(kpi.presupuesto_anual)"></span> ·
          <span x-text="kpi.avance_pct + '% del año'"></span>
        </p>
      </div>

      <div class="flex-1 min-w-[260px]">
        <div class="relative h-4 rounded-full overflow-hidden bg-slate-100">
          <div class="absolute inset-y-0 left-0 rounded-full transition-all bg-blue-600"
               :style="`width:${Math.min(kpi.avance_pct,100)}%`"></div>
          <div class="absolute inset-y-0 w-0.5 bg-slate-700/60"
               :style="`left:${kpi.esperado_pct}%`" title="Esperado a la fecha"></div>
        </div>
        <div class="flex justify-between text-xs mt-1.5 text-slate-400">
          <span>Avance real: <span class="font-semibold text-slate-600" x-text="kpi.avance_pct + '%'"></span></span>
          <span>Esperado hoy: <span class="font-semibold text-slate-600" x-text="kpi.esperado_pct + '%'"></span></span>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-4">
          <div class="rounded-xl p-3 bg-slate-50 border border-slate-100">
            <p class="text-xs text-slate-500 mb-1">Ritmo vs esperado</p>
            <p class="text-xl font-bold tabular-nums text-slate-900" x-text="kpi.ritmo_pct + '%'"></p>
          </div>
          <div class="rounded-xl p-3 bg-slate-50 border border-slate-100">
            <p class="text-xs text-slate-500 mb-1">Proyección cierre año</p>
            <p class="text-xl font-bold tabular-nums text-slate-900" x-text="money(kpi.proyeccion_cierre_anio)"></p>
          </div>
        </div>
      </div>
    </div>
  </x-ui.card>
  @else
  <x-ui.card class="p-6">
    <div class="flex items-center gap-3 text-slate-400">
      <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p class="text-sm">{{ $kpi['motivo'] ?? 'Sin datos de presupuesto para este año.' }}</p>
    </div>
  </x-ui.card>
  @endif

  <div class="grid lg:grid-cols-3 gap-5">

    {{-- ===== Pipeline personal ===== --}}
    <x-ui.card class="p-5 lg:col-span-2">
      <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Mi pipeline personal</p>
      <div class="space-y-4">
        <template x-for="e in pipeline" :key="e.estado_id">
          <div>
            <div class="flex items-center justify-between text-sm mb-1.5">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="`background:${e.color}`"></span>
                <span x-text="e.etapa" class="text-slate-700 font-medium"></span>
                <span class="text-xs px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 font-medium" x-text="e.num_negocios"></span>
              </div>
              <span class="tabular-nums text-slate-600 font-semibold" x-text="money(e.valor_etapa)"></span>
            </div>
            <div class="h-2 rounded-full overflow-hidden bg-slate-100">
              <div class="h-full rounded-full transition-all"
                   :style="`width:${barWidth(e.valor_etapa)}%;background:${e.color}`"></div>
            </div>
            <p class="text-xs mt-1"
               :class="e.dias_prom_en_etapa > 30 ? 'text-red-500' : 'text-slate-400'"
               x-text="`Prom. ${Math.round(e.dias_prom_en_etapa)} días en etapa`"></p>
          </div>
        </template>
        <div x-show="pipeline.length === 0" class="text-center text-sm text-slate-400 py-6">
          Sin negocios activos en el pipeline.
        </div>
      </div>
    </x-ui.card>

    {{-- ===== Posición en el equipo ===== --}}
    <x-ui.card class="p-5">
      <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Posición en el equipo</p>
      <p class="text-xs text-slate-400 mb-4">Ranking anónimo por logrado</p>
      <div class="text-center py-4">
        <p class="text-6xl font-bold text-slate-900 tabular-nums"
           x-text="ranking.mi_puesto ? `#${ranking.mi_puesto}` : '—'"></p>
        <p class="text-sm mt-2 text-slate-500" x-text="`de ${ranking.total} asesores`"></p>
      </div>
      <div class="mt-4 space-y-2 pt-4 border-t border-slate-100">
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">Líder del equipo</span>
          <span class="tabular-nums font-semibold text-slate-900" x-text="money(ranking.lider)"></span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">Mi logrado</span>
          <span class="tabular-nums font-semibold text-slate-900" x-text="money(ranking.mi_valor)"></span>
        </div>
        <div class="h-2 rounded-full overflow-hidden mt-2 bg-slate-100">
          <div class="h-full rounded-full bg-blue-600 transition-all"
               :style="`width:${ranking.lider > 0 ? Math.round(ranking.mi_valor/ranking.lider*100) : 0}%`"></div>
        </div>
      </div>
    </x-ui.card>

    {{-- ===== Próximas actividades ===== --}}
    <x-ui.card class="p-5 lg:col-span-2">
      <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Mis próximas actividades</p>
      <ul class="divide-y divide-slate-100">
        <template x-for="a in actividades" :key="a.id">
          <li class="py-3 flex items-center gap-3">
            <span class="text-xs px-2 py-0.5 rounded-full capitalize bg-slate-100 text-slate-600 font-medium shrink-0"
                  x-text="a.tipo"></span>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium truncate text-slate-900" x-text="a.entidad"></p>
              <p class="text-xs truncate text-slate-400" x-text="a.descripcion"></p>
            </div>
            <span class="text-xs tabular-nums whitespace-nowrap font-medium shrink-0"
                  :class="a.vencida ? 'text-red-500' : 'text-slate-400'"
                  x-text="fecha(a.proxima_fecha)"></span>
          </li>
        </template>
        <li x-show="actividades.length === 0" class="py-8 text-center text-sm text-slate-400">
          Sin actividades pendientes.
        </li>
      </ul>
    </x-ui.card>

    {{-- ===== Clientes sin contacto ===== --}}
    <x-ui.card class="p-5" x-data="scPager(@json($sinContacto))">
      <div class="flex items-center justify-between mb-4">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Clientes sin contacto</p>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600"
              x-text="total + (total === 1 ? ' cliente' : ' clientes')"></span>
      </div>
      <ul class="space-y-3">
        <template x-for="c in pagina" :key="c.id">
          <li class="flex items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="text-sm font-medium truncate text-slate-900" x-text="c.razon_social"></p>
              <p class="text-xs text-slate-400 tabular-nums"
                 x-text="new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(c.vlr_neto_facturado||0)"></p>
            </div>
            <span class="text-xs tabular-nums font-semibold text-red-500 shrink-0"
                  x-text="c.dias_sin_contacto ? c.dias_sin_contacto + 'd' : 'nunca'"></span>
          </li>
        </template>
        <li x-show="total === 0" class="text-center text-sm text-slate-400 py-4">
          Todos los clientes tienen contacto reciente.
        </li>
      </ul>
      <div x-show="totalPags > 1" class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100">
        <button @click="pag--" :disabled="pag === 1"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed transition">
          ← Anterior
        </button>
        <span class="text-xs text-slate-400" x-text="`${pag} / ${totalPags}`"></span>
        <button @click="pag++" :disabled="pag === totalPags"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed transition">
          Siguiente →
        </button>
      </div>
    </x-ui.card>

  </div>
</div>

@push('scripts')
<script>
function scPager(items, porPagina = 8) {
  return {
    items,
    pag: 1,
    porPagina,
    get total()     { return this.items.length },
    get totalPags() { return Math.max(1, Math.ceil(this.total / this.porPagina)) },
    get pagina()    { return this.items.slice((this.pag - 1) * this.porPagina, this.pag * this.porPagina) },
  }
}

function dashVendedor(){
  return {
    kpi:          @json($kpi),
    pipeline:     @json($pipeline),
    actividades:  @json($actividades),
    sinContacto:  @json($sinContacto),
    ranking:      @json($ranking),

    init(){},
    money(v){ return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(v||0) },
    fecha(s){ return s ? new Date(s).toLocaleDateString('es-CO',{day:'2-digit',month:'short'}) : '—' },
    barWidth(v){ const max=Math.max(...this.pipeline.map(p=>p.valor_etapa),1); return Math.round(v/max*100) },
    semaforoStyle(s){
      const m={verde:'background:var(--green-bg);color:var(--green)',
               amarillo:'background:var(--amber-bg);color:var(--amber)',
               rojo:'background:var(--red-bg);color:var(--red)',
               sin_presupuesto:'background:#f1f5f9;color:#64748b'};
      return m[s]||m.amarillo;
    },
    semaforoLabel(s){ return {verde:'A ritmo',amarillo:'Atención',rojo:'Atrasado',sin_presupuesto:'Sin presupuesto'}[s]||'—' },
  }
}
</script>
@endpush
</x-layouts.app>
