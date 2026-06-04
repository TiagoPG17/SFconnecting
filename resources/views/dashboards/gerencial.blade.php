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
      <h1 class="text-2xl font-bold text-slate-900 mt-1">Visión ejecutiva {{ $anio }}</h1>
    </div>
    <div class="flex gap-1 p-1 rounded-xl bg-white border border-slate-200">
      <template x-for="p in periodos" :key="p">
        <button @click="periodo=p"
                class="px-3 py-1.5 text-sm rounded-lg transition"
                :class="periodo===p ? 'text-white':''"
                :style="periodo===p ? 'background:var(--accent)':'color:var(--muted)'"
                x-text="p"></button>
      </template>
    </div>
  </div>

  {{-- ===== KPIs globales ===== --}}
  <div class="grid sm:grid-cols-4 gap-4 mb-5">
    <div class="dash-card p-4">
      <p class="text-xs text-slate-500">Presupuesto total</p>
      <p class="text-2xl font-bold mt-1 tnum text-slate-900" x-text="moneyShort(totales.presupuesto)"></p>
    </div>
    <div class="dash-card p-4">
      <p class="text-xs text-slate-500">Logrado YTD</p>
      <p class="text-2xl font-bold mt-1 tnum text-slate-900" x-text="moneyShort(totales.logrado)"></p>
    </div>
    <div class="dash-card p-4">
      <p class="text-xs text-slate-500">Cumplimiento</p>
      <p class="text-2xl font-bold mt-1 tnum text-slate-900" x-text="totales.cumplimiento+'%'"></p>
    </div>
    <div class="dash-card p-4">
      <p class="text-xs text-slate-500">Forecast cierre año</p>
      <p class="text-2xl font-bold mt-1 tnum text-slate-900" x-text="moneyShort(totales.forecast)"></p>
    </div>
  </div>

  <div class="grid lg:grid-cols-3 gap-5">

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

    {{-- ===== Retención y churn ===== --}}
    <div class="dash-card p-5">
      <h2 class="font-semibold text-slate-800 mb-4">Retención y churn</h2>
      <div class="space-y-3">
        <template x-for="b in churn" :key="b.banda">
          <div>
            <div class="flex justify-between text-sm mb-1">
              <span class="text-slate-700" x-text="b.banda.substring(2)"></span>
              <span class="tnum text-slate-400" x-text="`${b.num_clientes} · ${moneyShort(b.valor_en_banda)}`"></span>
            </div>
            <div class="h-2.5 rounded-full overflow-hidden bg-slate-100">
              <div class="h-full rounded-full" :style="`width:${bandaPct(b.valor_en_banda)}%;background:${bandaColor(b.banda)}`"></div>
            </div>
          </div>
        </template>
        <div x-show="churn.length === 0" class="text-center text-sm text-slate-400 py-4">Sin datos de recencia disponibles.</div>
      </div>
      <div class="mt-4 pt-4 border-t border-slate-100 text-sm flex justify-between">
        <span class="text-slate-500">Valor en riesgo</span>
        <span class="tnum font-semibold" style="color:var(--red)" x-text="moneyShort(valorEnRiesgo)"></span>
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
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
function dashGerencial(){
  return {
    periodos: ['Mes','Trimestre','Año'],
    periodo:  'Año',

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
    get valorEnRiesgo(){ return this.churn.filter(b=>b.banda>'2').reduce((s,b)=>s+Number(b.valor_en_banda),0) },
    get maxCiclo(){ return this.ciclo.length ? Math.max(...this.ciclo.map(c=>c.dias),1) : 1 },

    money(v){ return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(v||0) },
    moneyShort(v){ if(!v) return '$0'; v=Number(v); if(v>=1e9) return '$'+(v/1e9).toFixed(1)+'B'; if(v>=1e6) return '$'+(v/1e6).toFixed(0)+'M'; return '$'+v },
    barColor(p){ return p>=80?'var(--green)':p>=50?'var(--amber)':'var(--red)' },
    semaforoStyle(p){ return p>=80?'background:var(--green-bg);color:var(--green)':p>=50?'background:var(--amber-bg);color:var(--amber)':'background:var(--red-bg);color:var(--red)' },
    bandaColor(b){ return {'1':'#15803d','2':'#0ea5e9','3':'#f59e0b','4':'#b91c1c'}[b[0]]||'#94a3b8' },
    bandaPct(v){ const max=this.churn.length?Math.max(...this.churn.map(b=>Number(b.valor_en_banda)),1):1; return Math.round(Number(v)/max*100) },

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
              {label:'Llamadas', data:this.actividad.map(a=>a.llamada||0), backgroundColor:'#0f766e'},
              {label:'Visitas',  data:this.actividad.map(a=>a.visita||0),  backgroundColor:'#0ea5e9'},
              {label:'Emails',   data:this.actividad.map(a=>a.email||0),   backgroundColor:'#cbd5e1'},
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
