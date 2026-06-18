<x-layouts.app title="Calendario" :hide-page-title="true">

<style>
/* ── FullCalendar — overrides de legibilidad ─────────────────── */
:root {
  --fc-border-color:              #e2e8f0;
  --fc-today-bg-color:            #eff6ff;
  --fc-button-bg-color:           #f8fafc;
  --fc-button-border-color:       #e2e8f0;
  --fc-button-hover-bg-color:     #f1f5f9;
  --fc-button-hover-border-color: #cbd5e1;
  --fc-button-active-bg-color:    #1d4ed8;
  --fc-button-active-border-color:#1d4ed8;
  --fc-button-text-color:         #334155;
  --fc-page-bg-color:             transparent;
  --fc-neutral-bg-color:          #f8fafc;
  --fc-neutral-text-color:        #64748b;
  --fc-list-event-hover-bg-color: #f8fafc;
}

/* Base */
.fc { font-family: 'Inter', sans-serif; font-size: 14px; }

/* Barra de herramientas */
.fc .fc-toolbar         { padding: 16px 20px 12px; gap: 10px; flex-wrap: wrap; }
.fc .fc-toolbar-title   { font-size: 1.2rem; font-weight: 800; color: #1e293b; letter-spacing: -0.01em; }
.fc .fc-toolbar-chunk   { display: flex; align-items: center; gap: 6px; }

/* Botones */
.fc .fc-button {
  border-radius: 10px !important;
  font-weight: 600;
  font-size: 12.5px;
  padding: 6px 14px;
  box-shadow: none !important;
  outline: none !important;
  transition: all .15s;
}
.fc .fc-button:focus     { box-shadow: 0 0 0 3px rgba(29,78,216,.2) !important; }
.fc .fc-button-active,
.fc .fc-button-primary:not(:disabled).fc-button-active {
  background-color: #1d4ed8 !important;
  border-color:     #1d4ed8 !important;
  color: #fff !important;
}
.fc .fc-button-group .fc-button              { border-radius: 0 !important; }
.fc .fc-button-group .fc-button:first-child  { border-radius: 10px 0 0 10px !important; }
.fc .fc-button-group .fc-button:last-child   { border-radius: 0 10px 10px 0 !important; }

/* Cabecera de columnas (Lun, Mar…) */
.fc th.fc-col-header-cell {
  background: #f8fafc;
  font-size: 11px; font-weight: 800;
  text-transform: uppercase; letter-spacing: .07em;
  color: #94a3b8; padding: 12px 0;
}

/* Números de día */
.fc .fc-daygrid-day-number {
  font-size: 13.5px; font-weight: 600; color: #475569; padding: 6px 10px;
}
.fc .fc-day-today .fc-daygrid-day-number {
  background: #1d4ed8; color: #fff; border-radius: 50%;
  width: 30px; height: 30px;
  display: flex; align-items: center; justify-content: center;
  margin: 5px; padding: 0;
}
.fc .fc-day-today { background-color: #eff6ff !important; }

/* Celdas de día */
.fc .fc-daygrid-day { min-height: 90px; }

/* Eventos */
.fc-event {
  border-radius: 7px !important;
  font-size: 12.5px !important;
  font-weight: 600 !important;
  padding: 3px 7px !important;
  cursor: pointer;
  border-width: 1.5px !important;
  transition: filter .12s, transform .1s;
}
.fc-event:hover { filter: brightness(.91); transform: translateY(-1px); }


/* Lista */
.fc .fc-list-day-cushion  { background: #f1f5f9; padding: 10px 16px; }
.fc .fc-list-day-text,
.fc .fc-list-day-side-text{ font-size: 13px; font-weight: 700; color: #334155; }
.fc .fc-list-event td      { padding: 10px 16px; }
.fc .fc-list-event-title   { font-size: 13.5px; font-weight: 600; }
.fc .fc-list-event:hover td{ background: #f8fafc !important; }
.fc .fc-list-event-time    { font-size: 12.5px; color: #64748b; white-space: nowrap; }

/* Vista semana/día */
.fc .fc-timegrid-slot       { height: 48px; }
.fc .fc-timegrid-slot-label { font-size: 12px; color: #94a3b8; }

/* Scrollgrid */
.fc .fc-scrollgrid { border-color: #e2e8f0; }
.fc .fc-scrollgrid-section > td { border-color: #e2e8f0; }
</style>

<div
  x-data="calendarioCtrl({{ Js::from($asesores) }})"
  x-init="init()"
  class="-mt-2"
  style="height: calc(100vh - 6rem)">

  <div class="flex gap-5 h-full">

    {{-- ── Sidebar ────────────────────────────────────────────── --}}
    <div class="w-64 flex-shrink-0 flex flex-col gap-3 overflow-y-auto pb-2">

      {{-- Título --}}
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center flex-shrink-0 shadow-sm shadow-blue-200">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Calendario</h1>
            <p class="text-xs text-slate-400 mt-0.5">Actividades del equipo</p>
          </div>
        </div>
      </div>

      {{-- Filtros --}}
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-5 flex-1 min-h-0 overflow-y-auto space-y-5">

        {{-- Tipo de actividad --}}
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">
            Tipo de actividad
          </p>
          <div class="space-y-1.5">
            <template x-for="tipo in tiposList" :key="tipo.val">
              <label class="flex items-center gap-3 cursor-pointer group select-none p-2 rounded-xl hover:bg-slate-50 transition-colors">

                {{-- Checkbox custom coloreado --}}
                <span class="relative flex-shrink-0">
                  <input type="checkbox" :value="tipo.val" x-model="filtros.tipos"
                         @change="refetch()" class="sr-only peer">
                  <span class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all"
                        :style="filtros.tipos.includes(tipo.val)
                          ? `background:${tipo.color};border-color:${tipo.color}`
                          : `border-color:${tipo.color}40;background:#f8fafc`">
                    <svg x-show="filtros.tipos.includes(tipo.val)"
                         class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"/>
                    </svg>
                  </span>
                </span>

                {{-- Punto de color + Label --}}
                <span class="flex items-center gap-2 flex-1 min-w-0">
                  <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                        :style="`background:${tipo.color}`"></span>
                  <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900 transition-colors"
                        x-text="tipo.label"></span>
                </span>
              </label>
            </template>
          </div>
        </div>

        <div class="h-px bg-slate-100"></div>

        {{-- Estado — pills clickeables --}}
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">
            Estado
          </p>
          <div class="flex flex-wrap gap-1.5">
            <template x-for="opt in resultadosList" :key="opt.val">
              <button
                @click="filtros.resultado = opt.val; refetch()"
                class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all border"
                :class="filtros.resultado === opt.val
                  ? opt.activeClass
                  : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-700'"
                x-text="opt.label">
              </button>
            </template>
          </div>
        </div>

        {{-- Asesor --}}
        @role('admin|gerente')
        <template x-if="asesores.length > 0">
          <div>
            <div class="h-px bg-slate-100 mb-5"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">
              Asesor
            </p>
            <select x-model="filtros.asesor" @change="refetch()"
                    class="w-full text-sm rounded-xl border border-slate-200 px-3 py-2.5 text-slate-700 bg-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
              <option value="">Todos los asesores</option>
              <template x-for="a in asesores" :key="a.id">
                <option :value="a.id" x-text="a.name"></option>
              </template>
            </select>
          </div>
        </template>
        @endrole

        <div class="h-px bg-slate-100"></div>

        {{-- Leyenda visual --}}
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">
            Leyenda
          </p>
          <div class="space-y-2.5">
            <div class="flex items-center gap-3">
              <div class="w-8 h-4 rounded-md flex-shrink-0" style="background:#0ea5e9"></div>
              <span class="text-sm text-slate-600">Cita programada</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-8 h-4 rounded-md border-2 flex-shrink-0"
                   style="border-color:#0ea5e9;background:#0ea5e918"></div>
              <span class="text-sm text-slate-600">Actividad realizada</span>
            </div>
          </div>
        </div>

      </div>
    </div>

    {{-- ── Calendario ──────────────────────────────────────────── --}}
    <div class="flex-1 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div id="sfcalendar" class="h-full"></div>
    </div>

  </div>

  {{-- ── Panel de detalle (deslizante derecho) ──────────────────── --}}
  <div
    x-show="panelAbierto"
    x-transition:enter="transition ease-out duration-220"
    x-transition:enter-start="opacity-0 translate-x-8"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-160"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-8"
    @keydown.escape.window="cerrarPanel()"
    class="fixed right-0 top-12 bottom-0 z-40 w-96 bg-white border-l border-slate-100 shadow-2xl flex flex-col"
    x-cloak>

    {{-- Barra de color arriba --}}
    <div class="h-1.5 w-full flex-shrink-0"
         :style="'background: linear-gradient(90deg,' + colorActivo() + ',' + colorActivo() + '99)'">
    </div>

    {{-- Cabecera del panel --}}
    <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4">
      <div class="flex items-center gap-4 min-w-0">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
             :style="'background:' + colorActivo() + '18;border:2px solid ' + colorActivo() + '30'">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
               :style="'color:' + colorActivo()">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  :d="tipoIconos[eventoActivo?.extendedProps?.tipo] ?? tipoIconos.otro"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-xs font-bold uppercase tracking-widest mb-0.5"
             :style="'color:' + colorActivo()"
             x-text="tipoLabel(eventoActivo?.extendedProps?.tipo)"></p>
          <p class="text-base font-bold text-slate-900 leading-tight"
             x-text="eventoActivo?.extendedProps?.entidad ?? ''"></p>
        </div>
      </div>
      <button @click="cerrarPanel()"
              class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 p-2 rounded-xl transition-colors flex-shrink-0 mt-0.5">
        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    {{-- Cuerpo --}}
    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

      {{-- Tipo de registro: Cita programada / Actividad realizada --}}
      <template x-if="eventoActivo?.extendedProps?.esCita">
        <div class="flex items-center gap-3.5 bg-blue-50 border border-blue-100 rounded-2xl px-4 py-3.5">
          <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold text-blue-800">Cita programada</p>
            <p class="text-xs text-blue-500 mt-0.5">Esta actividad está agendada para realizarse</p>
          </div>
        </div>
      </template>
      <template x-if="!eventoActivo?.extendedProps?.esCita">
        <div class="flex items-center gap-3.5 bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3.5">
          <div class="w-10 h-10 rounded-xl bg-slate-500 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold text-slate-700">Actividad registrada</p>
            <p class="text-xs text-slate-400 mt-0.5">Seguimiento ya ejecutado con el cliente</p>
          </div>
        </div>
      </template>

      {{-- Fecha grande y legible --}}
      <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center flex-shrink-0 shadow-sm">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-xs text-slate-400 font-medium">Fecha y hora</p>
            <p class="text-sm font-bold text-slate-900 mt-0.5 leading-snug"
               x-text="fmtFecha(eventoActivo?.extendedProps?.fecha)"></p>
          </div>
        </div>
      </div>

      {{-- Asesor --}}
      <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center flex-shrink-0 shadow-sm">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div>
            <p class="text-xs text-slate-400 font-medium">Asesor responsable</p>
            <p class="text-sm font-bold text-slate-900 mt-0.5"
               x-text="eventoActivo?.extendedProps?.asesor || 'Sin asesor asignado'"></p>
          </div>
        </div>
      </div>

      {{-- Estado --}}
      <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2.5">Estado del seguimiento</p>
        <div class="flex items-center gap-3 p-3.5 rounded-2xl border"
             :class="resultadoBg(eventoActivo?.extendedProps?.resultado)">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
               :class="resultadoIconBg(eventoActivo?.extendedProps?.resultado)">
            {{-- exitoso --}}
            <svg x-show="eventoActivo?.extendedProps?.resultado === 'exitoso'"
                 class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            {{-- pendiente --}}
            <svg x-show="eventoActivo?.extendedProps?.resultado === 'pendiente'"
                 class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{-- no_contactado --}}
            <svg x-show="eventoActivo?.extendedProps?.resultado === 'no_contactado'"
                 class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            {{-- cancelado --}}
            <svg x-show="eventoActivo?.extendedProps?.resultado === 'cancelado'"
                 class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </div>
          <span class="text-sm font-bold"
                :class="resultadoTextColor(eventoActivo?.extendedProps?.resultado)"
                x-text="resultadoLabel(eventoActivo?.extendedProps?.resultado)"></span>
        </div>
      </div>

      {{-- Descripción --}}
      <div x-show="eventoActivo?.extendedProps?.descripcion">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2.5">Descripción</p>
        <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4">
          <p class="text-sm text-slate-700 leading-relaxed"
             x-text="eventoActivo?.extendedProps?.descripcion"></p>
        </div>
      </div>

    </div>

    {{-- Botones de acción --}}
    <div class="px-6 py-5 border-t border-slate-100 space-y-3">
      <template x-if="eventoActivo?.extendedProps?.url">
        <a :href="eventoActivo.extendedProps.url"
           class="flex items-center justify-center gap-2.5 w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-bold px-5 py-3 rounded-xl transition-colors shadow-sm shadow-blue-200">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
          </svg>
          Ver ficha del cliente
        </a>
      </template>
      <a href="{{ route('seguimientos.index') }}"
         class="flex items-center justify-center gap-2.5 w-full bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 text-sm font-semibold px-5 py-3 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h7"/>
        </svg>
        Ver todos los seguimientos
      </a>
    </div>
  </div>

  {{-- Overlay oscuro detrás del panel en móvil --}}
  <div x-show="panelAbierto"
       @click="cerrarPanel()"
       class="fixed inset-0 z-30 bg-slate-900/20 backdrop-blur-sm lg:hidden"
       x-cloak></div>

  {{-- ── Modal de día ──────────────────────────────────────────── --}}
  <div x-show="diaModalAbierto"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       @keydown.escape.window="cerrarDiaModal()"
       class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-cloak>

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
         @click="cerrarDiaModal()"></div>

    {{-- Panel --}}
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden z-10"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.stop>

      {{-- Encabezado --}}
      <div class="px-6 pt-6 pb-5 border-b border-slate-100">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Actividades del día</p>
            <h2 class="text-xl font-bold text-slate-900 capitalize" x-text="fmtDia(diaSeleccionado)"></h2>
            <p class="text-sm text-slate-400 mt-1">
              <span x-text="eventosDia.length"></span>
              <span x-text="eventosDia.length === 1 ? ' actividad registrada' : ' actividades registradas'"></span>
            </p>
          </div>
          <button @click="cerrarDiaModal()"
                  class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 p-2 rounded-xl transition-colors flex-shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      {{-- Lista de eventos --}}
      <div class="px-4 py-3 max-h-[55vh] overflow-y-auto">
        <template x-if="eventosDia.length === 0">
          <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
              <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <p class="text-sm font-semibold text-slate-500">Sin actividades este día</p>
            <p class="text-xs text-slate-400 mt-1">No hay seguimientos registrados</p>
          </div>
        </template>
        <template x-for="ev in eventosDia" :key="ev.id">
          <button @click="abrirEventoDesdeDia(ev)"
                  class="w-full flex items-center gap-3.5 p-3.5 rounded-2xl hover:bg-slate-50 active:bg-slate-100 transition-colors text-left mb-1 group">
            {{-- Ícono tipo --}}
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105"
                 :style="`background:${tipoColor(ev.extendedProps.tipo)}18`">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                   :style="`color:${tipoColor(ev.extendedProps.tipo)}`">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      :d="tipoIconos[ev.extendedProps.tipo] ?? tipoIconos.otro"/>
              </svg>
            </div>
            {{-- Datos --}}
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-slate-900 truncate"
                 x-text="ev.extendedProps.entidad"></p>
              <p class="text-xs text-slate-400 mt-0.5">
                <span class="font-semibold" :style="`color:${tipoColor(ev.extendedProps.tipo)}`"
                      x-text="tipoLabel(ev.extendedProps.tipo)"></span>
                <template x-if="ev.extendedProps.asesor">
                  <span> · <span x-text="ev.extendedProps.asesor"></span></span>
                </template>
              </p>
            </div>
            {{-- Badge estado --}}
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold flex-shrink-0"
                  :class="resultadoBadge(ev.extendedProps.resultado)"
                  x-text="resultadoLabel(ev.extendedProps.resultado)"></span>
            {{-- Flecha --}}
            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 flex-shrink-0 transition-colors"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
        </template>
      </div>

      {{-- Footer --}}
      <div class="px-6 py-4 border-t border-slate-100">
        <a href="{{ route('seguimientos.index') }}"
           class="flex items-center justify-center gap-2 w-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
          </svg>
          Ver todos los seguimientos
        </a>
      </div>

    </div>
  </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
function calendarioCtrl(asesores) {
  return {
    asesores,
    calendar:        null,
    panelAbierto:    false,
    eventoActivo:    null,
    diaModalAbierto: false,
    diaSeleccionado: null,
    eventosDia:      [],
    rawEventsByDate: {},

    filtros: {
      tipos:     ['llamada', 'reunion', 'email', 'visita', 'whatsapp', 'otro'],
      resultado: '',
      asesor:    '',
    },

    tiposList: [
      { val: 'llamada',  label: 'Llamada telefónica', color: '#0ea5e9' },
      { val: 'reunion',  label: 'Reunión presencial',  color: '#7c3aed' },
      { val: 'email',    label: 'Correo electrónico',  color: '#475569' },
      { val: 'visita',   label: 'Visita al cliente',   color: '#0d9488' },
      { val: 'whatsapp', label: 'WhatsApp',            color: '#16a34a' },
      { val: 'otro',     label: 'Otro',                color: '#94a3b8' },
    ],

    resultadosList: [
      { val: '',              label: 'Todos',           activeClass: 'bg-slate-800  border-slate-800  text-white' },
      { val: 'pendiente',     label: 'Pendiente',       activeClass: 'bg-yellow-500 border-yellow-500 text-white' },
      { val: 'exitoso',       label: 'Exitoso',         activeClass: 'bg-emerald-500 border-emerald-500 text-white' },
      { val: 'no_contactado', label: 'No contactado',   activeClass: 'bg-slate-500  border-slate-500  text-white' },
      { val: 'cancelado',     label: 'Cancelado',       activeClass: 'bg-red-500    border-red-500    text-white' },
    ],

    tipoIconos: {
      llamada:  'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
      reunion:  'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
      email:    'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
      visita:   'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
      whatsapp: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
      otro:     'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
    },


    init() {
      this.$nextTick(() => this.montarCalendario());
    },

    montarCalendario() {
      const el = document.getElementById('sfcalendar');
      if (!el) return;

      this.calendar = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        locale:      'es',
        height:      '100%',
        headerToolbar: {
          left:   'prev,next today',
          center: 'title',
          right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },
        buttonText: {
          today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Lista',
        },
        dayMaxEvents:    false,
        noEventsText:    'Sin actividades en este período',
        navLinks:        true,
        navLinkDayClick: (date, jsEvent) => {
          jsEvent.preventDefault();
          const key     = date.toISOString().slice(0, 10);
          const eventos = this.rawEventsByDate[key] ?? [];
          this.abrirDiaModal(date, eventos);
        },
        dateClick: (info) => {
          const key     = info.dateStr.slice(0, 10);
          const eventos = this.rawEventsByDate[key] ?? [];
          this.abrirDiaModal(info.date, eventos);
        },
        listDaySideFormat: false,
        listDayFormat:   { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

        // Evento con icono SVG según tipo
        eventContent: (arg) => {
          if (arg.event.extendedProps.isGroup) {
            const count = arg.event.extendedProps.count;
            return {
              html: `<div style="display:flex;align-items:center;gap:5px;overflow:hidden;white-space:nowrap;font-size:12.5px;font-weight:700;padding:2px 2px">
                       <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0">
                         <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                       </svg>
                       <span>${count} actividades</span>
                     </div>`,
            };
          }
          const tipo = arg.event.extendedProps.tipo ?? 'otro';
          const d    = this.tipoIconos[tipo] ?? this.tipoIconos.otro;
          const textColor = arg.event.textColor ?? '#fff';
          const time = arg.timeText
            ? `<span style="opacity:.75;font-size:10.5px;margin-right:1px;flex-shrink:0">${arg.timeText}</span>`
            : '';
          return {
            html: `<div style="display:flex;align-items:center;gap:3px;overflow:hidden;white-space:nowrap;font-size:12.5px;font-weight:600">
                     ${time}
                     <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="${textColor}"
                          stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                          style="flex-shrink:0;opacity:.9"><path d="${d}"/></svg>
                     <span style="overflow:hidden;text-overflow:ellipsis">${arg.event.title}</span>
                   </div>`,
          };
        },

        events: async (info, success, failure) => {
          try {
            const p = new URLSearchParams({
              start:     info.startStr,
              end:       info.endStr,
              resultado: this.filtros.resultado,
              asesor:    this.filtros.asesor,
            });
            this.filtros.tipos.forEach(t => p.append('tipos[]', t));

            const res = await fetch(`/calendario/eventos?${p}`, {
              headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const raw = await res.json();

            // Agrupar por fecha: 1 evento → pill normal; 2+ → pill resumen azul
            const byDate = {};
            raw.forEach(e => {
              const key = e.start.slice(0, 10);
              if (!byDate[key]) byDate[key] = [];
              byDate[key].push(e);
            });
            this.rawEventsByDate = byDate;

            const result = [];
            Object.entries(byDate).forEach(([date, evs]) => {
              if (evs.length === 1) {
                result.push(evs[0]);
              } else {
                result.push({
                  id:              'group-' + date,
                  start:           date,
                  backgroundColor: '#1d4ed8',
                  borderColor:     '#1d4ed8',
                  textColor:       '#fff',
                  extendedProps:   { isGroup: true, count: evs.length, eventos: evs, date },
                });
              }
            });

            success(result);
          } catch (e) { failure(e); }
        },

        eventClick: (info) => {
          info.jsEvent.preventDefault();
          if (info.event.extendedProps.isGroup) {
            const date = new Date(info.event.extendedProps.date + 'T12:00:00');
            this.abrirDiaModal(date, info.event.extendedProps.eventos);
            return;
          }
          this.eventoActivo = info.event;
          this.panelAbierto = true;
        },

        eventDidMount: (info) => {
          if (info.event.extendedProps.descripcion) {
            info.el.title = info.event.extendedProps.descripcion;
          }
        },

        datesSet: () => { if (this.panelAbierto) this.cerrarPanel(); },
      });

      this.calendar.render();
    },

    refetch()     { this.cerrarPanel(); this.calendar?.refetchEvents(); },
    cerrarPanel() { this.panelAbierto = false; this.eventoActivo = null; },

    colorActivo() {
      const m = { llamada:'#0ea5e9', reunion:'#7c3aed', email:'#475569', visita:'#0d9488', whatsapp:'#16a34a', otro:'#94a3b8' };
      return m[this.eventoActivo?.extendedProps?.tipo] ?? '#94a3b8';
    },

    tipoLabel(t) {
      const m = { llamada:'Llamada', reunion:'Reunión', email:'Email', visita:'Visita', whatsapp:'WhatsApp', otro:'Otro' };
      return m[t] ?? (t ?? '');
    },

    fmtFecha(iso) {
      if (!iso) return '—';
      const d = new Date(iso);
      const fecha = d.toLocaleDateString('es-CO', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
      const hora  = d.toLocaleTimeString('es-CO', { hour:'2-digit', minute:'2-digit', hour12:true });
      return `${fecha} · ${hora}`;
    },

    resultadoBg(r) {
      return { exitoso:'bg-emerald-50 border-emerald-100', pendiente:'bg-yellow-50 border-yellow-100',
               no_contactado:'bg-slate-50 border-slate-200', cancelado:'bg-red-50 border-red-100' }[r] ?? 'bg-slate-50 border-slate-200';
    },
    resultadoIconBg(r) {
      return { exitoso:'bg-emerald-500', pendiente:'bg-yellow-500',
               no_contactado:'bg-slate-500', cancelado:'bg-red-500' }[r] ?? 'bg-slate-400';
    },
    resultadoTextColor(r) {
      return { exitoso:'text-emerald-800', pendiente:'text-yellow-800',
               no_contactado:'text-slate-700', cancelado:'text-red-700' }[r] ?? 'text-slate-700';
    },
    resultadoLabel(r) {
      return { exitoso:'Exitoso', pendiente:'Pendiente', no_contactado:'No contactado', cancelado:'Cancelado' }[r] ?? (r ?? '—');
    },

    tipoColor(t) {
      return { llamada:'#0ea5e9', reunion:'#7c3aed', email:'#475569', visita:'#0d9488', whatsapp:'#16a34a', otro:'#94a3b8' }[t] ?? '#94a3b8';
    },

    fmtDia(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString('es-CO', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
    },

    resultadoBadge(r) {
      return { exitoso:'bg-emerald-100 text-emerald-700', pendiente:'bg-yellow-100 text-yellow-700',
               no_contactado:'bg-slate-100 text-slate-600', cancelado:'bg-red-100 text-red-700' }[r] ?? 'bg-slate-100 text-slate-600';
    },

    abrirDiaModal(date, eventos) {
      this.cerrarPanel();
      this.diaSeleccionado = date;
      this.eventosDia      = eventos;
      this.diaModalAbierto = true;
    },

    cerrarDiaModal() {
      this.diaModalAbierto = false;
      this.$nextTick(() => { this.diaSeleccionado = null; this.eventosDia = []; });
    },

    abrirEventoDesdeDia(ev) {
      this.cerrarDiaModal();
      this.$nextTick(() => { this.eventoActivo = ev; this.panelAbierto = true; });
    },
  };
}
</script>
@endpush
</x-layouts.app>
