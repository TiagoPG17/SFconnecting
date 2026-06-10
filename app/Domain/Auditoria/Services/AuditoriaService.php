<?php

declare(strict_types=1);

namespace App\Domain\Auditoria\Services;

use App\Domain\Auditoria\Repositories\AuditoriaRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditoriaService
{
    public function __construct(
        private readonly AuditoriaRepositoryInterface $repository,
    ) {}

    public function listar(array $filtros): LengthAwarePaginator
    {
        $limpios = array_filter([
            'usuario_id' => $filtros['usuario_id'] ?? null,
            'modulo'     => $filtros['modulo']      ?? null,
            'accion'     => $filtros['accion']      ?? null,
            'desde'      => $filtros['desde']       ?? null,
            'hasta'      => $filtros['hasta']       ?? null,
        ]);

        return $this->repository->paginar($limpios);
    }
}
