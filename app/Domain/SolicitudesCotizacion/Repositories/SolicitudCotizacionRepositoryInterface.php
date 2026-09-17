<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCotizacion\Repositories;

use App\Domain\SolicitudesCotizacion\Models\SolicitudCotizacion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SolicitudCotizacionRepositoryInterface
{
    /** Totales por comercial (consulta 1): num. solicitudes, de clientes, sin asignar, escalas, partes. */
    public function resumenPorComercial(array $filtros = []): Collection;

    /** Listado nivel solicitud (consultas 1/2 del primer documento), paginado. */
    public function solicitudes(array $filtros = [], int $porPagina = 20): LengthAwarePaginator;

    /** Listado nivel escala con precio (consulta 3), paginado. */
    public function escalas(array $filtros = [], int $porPagina = 30): LengthAwarePaginator;

    /** Detalle de una solicitud con sus escalas cargadas. */
    public function buscarSolicitud(string $nroSolicitud): ?SolicitudCotizacion;

    /** Pares vendedor/nombre_vendedor distintos, para poblar el filtro. */
    public function vendedoresDisponibles(): Collection;

    /** Auditoría de solicitudes dadas de baja en SGP (soft delete). */
    public function bajas(int $porPagina = 20): LengthAwarePaginator;

    /** Solicitudes activas sin cliente asignado — candidatas a convertirse en Prospecto. */
    public function candidatosProspecto(?string $buscar = null, int $limite = 50): Collection;

    /** Solicitudes activas con cliente asignado — candidatas a convertirse en Negocio. */
    public function candidatosNegocio(?string $buscar = null, int $limite = 50): Collection;

    /** Suma de valor_total_escala (escalas activas) por nro_solicitud, para prellenar valor_estimado. */
    public function valorTotalPorSolicitud(array $nrosSolicitud): Collection;

    /** Escalas activas de una solicitud puntual, para que el comercial elija cuál usar al crear el Negocio. */
    public function escalasActivasDeSolicitud(string $nroSolicitud): Collection;
}
