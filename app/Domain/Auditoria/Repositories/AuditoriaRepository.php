<?php

declare(strict_types=1);

namespace App\Domain\Auditoria\Repositories;

use App\Domain\Auditoria\Models\ActividadLog;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditoriaRepository implements AuditoriaRepositoryInterface
{
    public function paginar(array $filtros, int $porPagina = 50): LengthAwarePaginator
    {
        return ActividadLog::with('usuario')
            ->when(isset($filtros['usuario_id']), fn ($q) => $q->where('user_id', $filtros['usuario_id']))
            ->when(isset($filtros['modulo']),     fn ($q) => $q->where('modulo', $filtros['modulo']))
            ->when(isset($filtros['accion']),     fn ($q) => $q->where('accion', $filtros['accion']))
            ->when(isset($filtros['desde']),      fn ($q) => $q->whereDate('created_at', '>=', $filtros['desde']))
            ->when(isset($filtros['hasta']),      fn ($q) => $q->whereDate('created_at', '<=', $filtros['hasta']))
            ->orderByDesc('created_at')
            ->paginate($porPagina)
            ->withQueryString();
    }
}
