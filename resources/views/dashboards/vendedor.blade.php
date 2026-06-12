<x-layouts.app title="Mi desempeño · CRM">

<style>
:root{
  --ink:#1a1d23;--muted:#6b7280;--line:#e8e6e1;--paper:#f6f4ef;--card:#fffdf9;
  --green:#15803d;--green-bg:#dcfce7;--amber:#b45309;--amber-bg:#fef3c7;
  --red:#b91c1c;--red-bg:#fee2e2;--accent:#0f766e;
}
.dash-card{background:var(--card);border:1px solid var(--line);border-radius:14px}
.tnum{font-variant-numeric:tabular-nums}
</style>

<div x-data="dashVendedor()" x-init="init()">

  {{-- ===== Encabezado ===== --}}
  <div class="flex items-end justify-between mb-7">
    <div>
      <p class="text-xs uppercase tracking-widest text-slate-400">Dashboard del vendedor</p>
      <h1 class="text-2xl font-bold text-slate-900 mt-1">
        Mi desempeño —
        @if($periodo === 'mes') {{ now()->locale('es')->translatedFormat('F Y') }}
        @elseif($periodo === 'trimestre') T{{ (int) ceil(now()->month / 3) }} {{ $anio }}
        @else {{ $anio }}
        @endif
      </h1>
    </div>
    <div class="flex items-center gap-2">
      @if(count($companias) > 1)
      <div class="flex gap-1 p-1 rounded-xl bg-white border border-slate-200">
        @foreach($companias as $cia)
          <a href="{{ request()->fullUrlWithQuery(['compania' => $cia]) }}"
             class="px-3 py-1.5 text-sm rounded-lg transition {{ $compania === $cia ? 'text-white' : 'text-slate-500 hover:bg-slate-100' }}"
             style="{{ $compania === $cia ? 'background:#7c3aed' : '' }}">
            CIA {{ $cia }}
          </a>
        @endforeach
      </div>
      @endif
      <div class="flex gap-1 p-1 rounded-xl bg-white border border-slate-200">
        @foreach([['anio','Año'],['trimestre','Trimestre'],['mes','Mes']] as [$val,$label])
          <a href="{{ request()->fullUrlWithQuery(['periodo' => $val]) }}"
             class="px-3 py-1.5 text-sm rounded-lg transition {{ $periodo === $val ? 'text-white' : 'text-slate-500 hover:bg-slate-100' }}"
             style="{{ $periodo === $val ? 'background:var(--accent)' : '' }}">
            {{ $label }}
          </a>
        @endforeach
      </div>
    </div>
  </div>

  {{-- ===== Tarjeta hero: presupuesto vs logrado ===== --}}
  @if($kpi['ok'] ?? false)
  <div class="dash-card p-6 mb-5">
    <div class="flex flex-wrap items-start justify-between gap-6">
      <div class="min-w-[220px]">
        <div class="flex items-center gap-2">
          <span class="text-sm text-slate-500">Presupuesto anual vs logrado</span>
          <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                :style="semaforoStyle(kpi.semaforo)" x-text="semaforoLabel(kpi.semaforo)"></span>
        </div>
        <p class="text-4xl font-bold mt-2 tnum text-slate-900" x-text="money(kpi.logrado_ytd)"></p>
        <p class="text-sm mt-1 text-slate-500">
          de <span class="tnum" x-text="money(kpi.presupuesto_anual)"></span> ·
          <span x-text="kpi.avance_pct + '% del año'"></span>
        </p>
      </div>
      <div class="flex-1 min-w-[260px]">
        <div class="relative h-5 rounded-full overflow-hidden bg-slate-100">
          <div class="absolute inset-y-0 left-0 rounded-full transition-all"
               :style="`width:${Math.min(kpi.avance_pct,100)}%;background:var(--accent)`"></div>
          <div class="absolute inset-y-0 w-[2px] bg-slate-700"
               :style="`left:${kpi.esperado_pct}%`" title="Esperado a la fecha"></div>
        </div>
        <div class="flex justify-between text-xs mt-2 text-slate-500">
          <span>Avance real</span>
          <span x-text="`Esperado a hoy: ${kpi.esperado_pct}%`"></span>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-4">
          <div class="rounded-lg p-3 bg-slate-50">
            <p class="text-xs text-slate-500">Ritmo vs esperado</p>
            <p class="text-lg font-semibold tnum text-slate-900" x-text="kpi.ritmo_pct + '%'"></p>
          </div>
          <div class="rounded-lg p-3 bg-slate-50">
            <p class="text-xs text-slate-500">Proyección cierre año</p>
            <p class="text-lg font-semibold tnum text-slate-900" x-text="money(kpi.proyeccion_cierre_anio)"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
  @else
  <div class="dash-card p-6 mb-5 text-center text-slate-400 text-sm">
    {{ $kpi['motivo'] ?? 'Sin datos de presupuesto para este año.' }}
  </div>
  @endif

  <div class="grid lg:grid-cols-3 gap-5">

    {{-- ===== Pipeline personal ===== --}}
    <div class="dash-card p-5 lg:col-span-2">
      <h2 class="font-semibold text-slate-800 mb-4">Mi pipeline personal</h2>
      <div class="space-y-3">
        <template x-for="e in pipeline" :key="e.estado_id">
          <div>
            <div class="flex items-center justify-between text-sm mb-1">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" :style="`background:${e.color}`"></span>
                <span x-text="e.etapa" class="text-slate-700"></span>
                <span class="text-xs px-1.5 rounded bg-slate-100 text-slate-500" x-text="e.num_negocios"></span>
              </div>
              <span class="tnum text-slate-500" x-text="money(e.valor_etapa)"></span>
            </div>
            <div class="h-2 rounded-full overflow-hidden bg-slate-100">
              <div class="h-full rounded-full"
                   :style="`width:${barWidth(e.valor_etapa)}%;background:${e.color}`"></div>
            </div>
            <p class="text-xs mt-1"
               :style="e.dias_prom_en_etapa > 30 ? 'color:var(--red)' : 'color:var(--muted)'"
               x-text="`Prom. ${Math.round(e.dias_prom_en_etapa)} días en etapa`"></p>
          </div>
        </template>
        <div x-show="pipeline.length === 0" class="text-center text-sm text-slate-400 py-6">Sin negocios activos en el pipeline.</div>
      </div>
    </div>

    {{-- ===== Posición en el equipo ===== --}}
    <div class="dash-card p-5">
      <h2 class="font-semibold text-slate-800 mb-1">Mi posición en el equipo</h2>
      <p class="text-xs text-slate-400 mb-4">Ranking anónimo por logrado</p>
      <div class="text-center py-3">
        <p class="text-5xl font-bold text-slate-900" x-text="ranking.mi_puesto ? `#${ranking.mi_puesto}` : '—'"></p>
        <p class="text-sm mt-1 text-slate-500" x-text="`de ${ranking.total} asesores`"></p>
      </div>
      <div class="mt-4 space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">Líder del equipo</span>
          <span class="tnum font-medium text-slate-900" x-text="money(ranking.lider)"></span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">Mi logrado</span>
          <span class="tnum font-medium text-slate-900" x-text="money(ranking.mi_valor)"></span>
        </div>
        <div class="h-2 rounded-full overflow-hidden mt-1 bg-slate-100">
          <div class="h-full rounded-full" style="background:var(--accent)"
               :style="`width:${ranking.lider > 0 ? Math.round(ranking.mi_valor/ranking.lider*100) : 0}%`"></div>
        </div>
      </div>
    </div>

    {{-- ===== Próximas actividades ===== --}}
    <div class="dash-card p-5 lg:col-span-2">
      <h2 class="font-semibold text-slate-800 mb-4">Mis próximas actividades</h2>
      <ul class="divide-y divide-slate-100">
        <template x-for="a in actividades" :key="a.id">
          <li class="py-2.5 flex items-center gap-3">
            <span class="text-xs px-2 py-0.5 rounded-full capitalize bg-slate-100 text-slate-500" x-text="a.tipo"></span>
            <div class="flex-1 min-w-0">
              <p class="text-sm truncate text-slate-900" x-text="a.entidad"></p>
              <p class="text-xs truncate text-slate-400" x-text="a.descripcion"></p>
            </div>
            <span class="text-xs tnum whitespace-nowrap"
                  :style="a.vencida ? 'color:var(--red)' : 'color:var(--muted)'"
                  x-text="fecha(a.proxima_fecha)"></span>
          </li>
        </template>
        <li x-show="actividades.length === 0" class="py-6 text-center text-sm text-slate-400">Sin actividades pendientes.</li>
      </ul>
    </div>

    {{-- ===== Clientes sin contacto ===== --}}
    <div class="dash-card p-5" x-data="scPager(@json($sinContacto))">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-slate-800">Clientes sin contacto</h2>
        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
              style="background:var(--red-bg);color:var(--red)" x-text="total"></span>
      </div>
      <ul class="space-y-2.5">
        <template x-for="c in pagina" :key="c.id">
          <li class="flex items-center justify-between">
            <div class="min-w-0">
              <p class="text-sm truncate text-slate-900" x-text="c.razon_social"></p>
              <p class="text-xs text-slate-400" x-text="new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(c.vlr_neto_facturado||0)"></p>
            </div>
            <span class="text-xs tnum font-semibold" style="color:var(--red)"
                  x-text="c.dias_sin_contacto ? c.dias_sin_contacto+'d' : 'nunca'"></span>
          </li>
        </template>
        <li x-show="total === 0" class="text-center text-sm text-slate-400 py-4">Todos los clientes tienen contacto reciente.</li>
      </ul>
      <div x-show="totalPags > 1" class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100">
        <button @click="pag--" :disabled="pag === 1"
                class="px-3 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed transition">
          ← Anterior
        </button>
        <span class="text-xs text-slate-400" x-text="`${pag} / ${totalPags}`"></span>
        <button @click="pag++" :disabled="pag === totalPags"
                class="px-3 py-1 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed transition">
          Siguiente →
        </button>
      </div>
    </div>

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
