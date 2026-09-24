{{-- Tarjeta de pendientes por facturar. Usa la variable Alpine `t` (ver `tarjetasPendientes` en informeComercial()). --}}
<div class="dash-card p-4">
  <p class="text-xs text-slate-500" x-text="t.titulo"></p>
  <p class="text-2xl font-bold mt-1 tnum" :style="'color:' + t.color" x-text="money(t.valor)"></p>
  <p class="text-[11px] text-slate-400 mt-0.5" x-text="t.pedidos + ' pedidos · ' + t.detalle"></p>
  <div x-show="filtro.cia === 0" class="mt-2 pt-2 border-t border-slate-100 space-y-0.5">
    <template x-for="r in t.porCia" :key="t.clave + '-' + r.compania">
      <div class="flex items-center justify-between text-[11px]">
        <span class="text-slate-500" x-text="ciaName(r.compania) + ' · ' + r.pedidos + ' pedidos'"></span>
        <span class="tnum font-medium text-slate-700" x-text="money(r.valor)"></span>
      </div>
    </template>
  </div>
</div>
