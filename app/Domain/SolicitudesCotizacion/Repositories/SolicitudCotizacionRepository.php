<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCotizacion\Repositories;

use App\Domain\SolicitudesCotizacion\Models\SolicitudCotizacion;
use App\Domain\SolicitudesCotizacion\Models\SolicitudCotizacionEscala;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SolicitudCotizacionRepository implements SolicitudCotizacionRepositoryInterface
{
    public function resumenPorComercial(array $filtros = []): Collection
    {
        $query = SolicitudCotizacion::query()->activo();

        $this->aplicarRangoFechas($query, $filtros);

        return $query
            ->selectRaw('vendedor, nombre_vendedor')
            ->selectRaw('COUNT(*) as num_solicitudes')
            ->selectRaw("SUM(CASE WHEN situacion_cliente = 'CLIENTE ASIGNADO' THEN 1 ELSE 0 END) as de_clientes")
            ->selectRaw("SUM(CASE WHEN situacion_cliente IN ('VARIOS', 'NO REGISTRADO') THEN 1 ELSE 0 END) as sin_asignar")
            ->selectRaw('SUM(escalas) as total_escalas')
            ->selectRaw('SUM(partes) as total_partes')
            ->groupBy('vendedor', 'nombre_vendedor')
            ->orderByDesc('num_solicitudes')
            ->get();
    }

    public function solicitudes(array $filtros = [], int $porPagina = 20): LengthAwarePaginator
    {
        $query = SolicitudCotizacion::query()
            ->activo()
            ->delVendedor($filtros['vendedor'] ?? null)
            ->buscarCliente($filtros['buscar'] ?? null);

        if (! empty($filtros['situacion_cliente'])) {
            $query->where('situacion_cliente', $filtros['situacion_cliente']);
        }

        $this->aplicarRangoFechas($query, $filtros);

        return $query->orderByDesc('fecha_solicitud')->orderByDesc('nro_solicitud')
            ->paginate($porPagina)->withQueryString();
    }

    public function escalas(array $filtros = [], int $porPagina = 30): LengthAwarePaginator
    {
        $query = SolicitudCotizacionEscala::query()
            ->activo()
            ->delVendedor($filtros['vendedor'] ?? null)
            ->buscarCliente($filtros['buscar'] ?? null);

        if (! empty($filtros['situacion_escala'])) {
            $query->where('situacion_escala', $filtros['situacion_escala']);
        }

        $this->aplicarRangoFechas($query, $filtros);

        return $query->orderBy('vendedor')->orderBy('nro_solicitud')->orderBy('escala')
            ->paginate($porPagina)->withQueryString();
    }

    public function buscarSolicitud(string $nroSolicitud): ?SolicitudCotizacion
    {
        return SolicitudCotizacion::query()
            ->where('nro_solicitud', $nroSolicitud)
            ->with(['escalasDetalle' => fn ($q) => $q->orderBy('escala')])
            ->first();
    }

    public function vendedoresDisponibles(): Collection
    {
        return SolicitudCotizacion::query()
            ->activo()
            ->select('vendedor', 'nombre_vendedor')
            ->distinct()
            ->orderBy('nombre_vendedor')
            ->get();
    }

    public function bajas(int $porPagina = 20): LengthAwarePaginator
    {
        return SolicitudCotizacion::query()
            ->eliminado()
            ->orderByDesc('fecha_baja')
            ->paginate($porPagina);
    }

    public function candidatosVinculacion(?string $buscar = null, ?string $vendedorSgp = null, int $limite = 100): Collection
    {
        return SolicitudCotizacion::query()
            ->activo()
            ->ultimosDias(30)
            ->buscarCliente($buscar)
            ->delVendedor($vendedorSgp)
            ->orderByDesc('fecha_solicitud')
            ->limit($limite)
            ->get([
                'nro_solicitud', 'fecha_solicitud', 'vendedor', 'nombre_vendedor',
                'nit', 'cliente', 'cliente_digitado', 'situacion_cliente',
                'tipo_cotizacion', 'descripcion', 'escalas', 'escalas_con_precio', 'partes',
            ]);
    }

    public function valorTotalPorSolicitud(array $nrosSolicitud): Collection
    {
        if (empty($nrosSolicitud)) {
            return collect();
        }

        return SolicitudCotizacionEscala::query()
            ->activo()
            ->whereIn('nro_solicitud', $nrosSolicitud)
            ->groupBy('nro_solicitud')
            ->selectRaw('nro_solicitud, SUM(valor_total_escala) as valor_total')
            ->get()
            ->pluck('valor_total', 'nro_solicitud');
    }

    public function escalasActivasDeSolicitud(string $nroSolicitud): Collection
    {
        return SolicitudCotizacionEscala::query()
            ->activo()
            ->where('nro_solicitud', $nroSolicitud)
            ->orderBy('escala')
            ->get();
    }

    private function aplicarRangoFechas(Builder $query, array $filtros): void
    {
        if (! empty($filtros['mes_actual'])) {
            $query->mesActual();

            return;
        }

        if (! empty($filtros['desde'])) {
            $query->whereDate('fecha_solicitud', '>=', $filtros['desde']);
        }

        if (! empty($filtros['hasta'])) {
            $query->whereDate('fecha_solicitud', '<=', $filtros['hasta']);
        }
    }
}
