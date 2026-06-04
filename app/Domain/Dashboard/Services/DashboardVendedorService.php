<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Services;

use App\Domain\Dashboard\Repositories\DashboardVendedorRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Throwable;

class DashboardVendedorService
{
    public function __construct(
        private readonly DashboardVendedorRepositoryInterface $repo,
        private readonly int $asesorId,
        private readonly int $compania,
        private readonly int $anio,
        private readonly array $meses,
    ) {}

    public function presupuestoVsLogrado(): array
    {
        $presupuesto  = (float) ($this->repo->presupuestoVendedor($this->asesorId, $this->compania, $this->anio)?->presupuesto ?? 0);
        $codVendedor  = $this->repo->codVendedorSiesa($this->asesorId, $this->compania);

        if (! $codVendedor) {
            return ['ok' => false, 'motivo' => 'Vendedor sin mapear en SIESA'];
        }

        try {
            $logrado = $this->repo->logradoYtd($codVendedor, $this->compania, $this->meses);
        } catch (Throwable) {
            $logrado = 0.0;
        }

        // Calcular métricas de ritmo según el período seleccionado
        $primerMes = Carbon::createFromFormat('Y-m', collect($this->meses)->min())->startOfMonth();
        $ultimoMes = Carbon::createFromFormat('Y-m', collect($this->meses)->max())->endOfMonth();
        $hoy       = now()->min($ultimoMes);

        $diasTotal   = $primerMes->diffInDays($ultimoMes) + 1;
        $diasTrx     = max(1, $primerMes->diffInDays($hoy) + 1);
        $esperadoPct = round($diasTrx / $diasTotal * 100, 1);

        // Presupuesto proporcional al período
        $diasAnio        = Carbon::create($this->anio, 1, 1)->diffInDays(Carbon::create($this->anio, 12, 31)) + 1;
        $presupuestoPer  = round($presupuesto * $diasTotal / $diasAnio);

        $avancePct = $presupuestoPer > 0 ? round($logrado * 100 / $presupuestoPer, 1) : null;
        $base      = $presupuestoPer * $diasTrx / $diasTotal;
        $ritmoPct  = $base > 0 ? round($logrado * 100 / $base, 1) : null;

        return [
            'ok'                     => true,
            'presupuesto_anual'      => $presupuestoPer,
            'logrado_ytd'            => $logrado,
            'avance_pct'             => $avancePct ?? 0,
            'esperado_pct'           => $esperadoPct,
            'ritmo_pct'              => $ritmoPct ?? 0,
            'proyeccion_cierre_anio' => $diasTrx > 0 ? round($logrado / $diasTrx * $diasTotal) : $logrado,
            'semaforo'               => $ritmoPct === null ? 'sin_presupuesto'
                                        : ($ritmoPct >= 100 ? 'verde' : ($ritmoPct >= 80 ? 'amarillo' : 'rojo')),
        ];
    }

    public function proximasActividades(): Collection
    {
        return $this->repo->proximasActividades($this->asesorId, 15)
            ->map(fn ($s) => [
                'id'            => $s->id,
                'tipo'          => $s->tipo,
                'descripcion'   => $s->descripcion,
                'proxima_fecha' => $s->proxima_fecha,
                'entidad'       => $s->cliente?->razon_social ?? $s->prospecto?->empresa ?? '—',
                'vencida'       => $s->proxima_fecha?->isPast() ?? false,
            ]);
    }

    public function pipelinePersonal(): Collection
    {
        return $this->repo->etapasPipelinePersonal($this->asesorId)
            ->map(fn ($e) => [
                'estado_id'          => $e->id,
                'etapa'              => $e->nombre,
                'color'              => $e->color,
                'num_negocios'       => $e->num_negocios,
                'valor_etapa'        => (float) $e->valor_etapa,
                'dias_prom_en_etapa' => $this->repo->diasPromEtapa($e->id, $this->asesorId),
            ]);
    }

    public function clientesSinContacto(): Collection
    {
        $clientes = $this->repo->clientesSinContacto30($this->asesorId);

        if ($clientes->isEmpty()) {
            return collect();
        }

        try {
            $nits     = $clientes->pluck('nit')->all();
            $recencia = $this->repo->recenciaClientes($nits, $this->compania)->keyBy('NIT');
        } catch (Throwable) {
            $recencia = collect();
        }

        return $clientes
            ->map(fn ($c) => [
                'id'                 => $c->id,
                'razon_social'       => $c->razon_social,
                'dias_sin_contacto'  => $c->dias_sin_contacto,
                'vlr_neto_facturado' => (float) ($recencia[$c->nit]?->VLR_NETO_FACTURADO ?? 0),
            ])
            ->sortByDesc('vlr_neto_facturado')
            ->values();
    }

    public function posicionEnEquipo(): array
    {
        $codVendedor = $this->repo->codVendedorSiesa($this->asesorId, $this->compania);

        try {
            $rows = $this->repo->rankingVendedores($this->compania, $this->meses);
        } catch (Throwable) {
            $rows = collect();
        }

        $idx = $rows->search(fn ($r) => $r->COD_VENDEDOR === $codVendedor);

        return [
            'mi_puesto' => $idx === false ? null : $idx + 1,
            'total'     => $rows->count(),
            'lider'     => (float) ($rows->first()?->logrado ?? 0),
            'mi_valor'  => (float) ($rows->firstWhere('COD_VENDEDOR', $codVendedor)?->logrado ?? 0),
        ];
    }
}
