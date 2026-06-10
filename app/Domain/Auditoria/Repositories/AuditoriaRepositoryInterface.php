<?php

declare(strict_types=1);

namespace App\Domain\Auditoria\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface AuditoriaRepositoryInterface
{
    public function paginar(array $filtros, int $porPagina = 50): LengthAwarePaginator;
}
